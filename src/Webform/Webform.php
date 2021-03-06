<?php

namespace Drupal\little_helpers\Webform;

use Drupal\little_helpers\System\FormRedirect;

class Webform {
  public $node;
  protected $webform;
  public $nid;

  public function __construct($node) {
    $this->node = $node;
    $this->webform = &$node->webform;

    if (!isset($this->webform['cids'])) {
      foreach ($this->webform['components'] as $component) {
        $this->webform['cids'][$component['form_key']] = (int) $component['cid'];
      }
    }
  }

  public static function fromNode(\stdClass $node) {
    return new static($node);
  }

  /**
   * Get the component array by it's form_key.
   *
   * @param string $form_key
   *   form_key of the component.
   *
   * @return array
   *   The component array (as in {webform_component}).
   */
  public function &componentByKey($form_key) {
    if (isset($this->webform['cids'][$form_key])) {
      return $this->webform['components'][$this->webform['cids'][$form_key]];
    }
    // Make return by reference work.
    $return = array();
    return $return;
  }

  /**
   * Get the component array by it's component ID.
   *
   * @param int $cid
   *   The component id as in {webform_component}.
   * @return &array
   *   The component array.
   */
  public function &component($cid) {
    return $this->webform['components'][$cid];
  }

  public function componentsByType($type) {
    $components = array();
    foreach ($this->webform['components'] as $cid => &$c) {
      if ($c['type'] == $type) {
        $components[$cid] = &$c;
      }
    }
    return $components;
  }

  /**
   * Get the redirect_url for this webform as used by the submit handler.
   *
   * This is mainly a c&p of the relevant parts of
   * @see webform_client_form_submit().
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   An optional submission object used to replace tokens in the redirect URL.
   *
   * @return array
   *   The form redirect calculated from the webform config and submission.
   */
  public function getRedirect(Submission $submission = NULL) {
    $node = $this->node;
    $redirect_url = $node->webform['redirect_url'];

    // Clean up the redirect URL and filter it for webform tokens.
    $redirect_url = trim($node->webform['redirect_url']);
    if ($submission) {
      $redirect_url = _webform_filter_values($redirect_url, $node, $submission, NULL, FALSE, TRUE);
    }

    // Remove the domain name from the redirect.
    $redirect_url = preg_replace('/^' . preg_quote($GLOBALS['base_url'], '/') . '\//', '', $redirect_url);

    if ($redirect_url == '<none>') {
      $redirect = new FormRedirect(['path' => NULL]);
    }
    elseif ($redirect_url == '<confirmation>') {
      $options = array();
      if ($submission) {
        $options['query']['sid'] = $submission->sid;
        if ((int) $GLOBALS['user']->uid === 0) {
          $options['query']['token'] = webform_get_submission_access_token($submission);
        }
      }
      $redirect = new FormRedirect(['path' => "node/{$node->nid}/done"] + $options);
    }
    elseif (valid_url($redirect_url, TRUE)) {
      $redirect = FormRedirect::fromFormStateRedirect($redirect_url);
    }
    elseif ($redirect_url && strpos($redirect_url, 'http') !== 0) {
      $parts = drupal_parse_url($redirect_url);
      if ($submission) {
        $parts['query']['sid'] = $submission->sid;
      }
      $redirect = new FormRedirect($parts);
    }
    else {
      $redirect = FormRedirect::fromFormStateRedirect($redirect_url);
    }
    drupal_alter('webform_redirect', $redirect, $submission);
    return $redirect->toFormStateRedirect();
  }

  /**
   * Create a submission-object from a webform_client_form $form_state.
   *
   * This is basically a copy & paste from webform_client_form_submit().
   */
  public function formStateToSubmission(&$form_state) {
    $node = $this->node;
    $form_state += ['values' => ['submitted' => [], 'details' => ['sid' => NULL, 'uid' => $GLOBALS['user']->uid]]];
    $sid = $form_state['values']['details']['sid'] ? (int) $form_state['values']['details']['sid'] : NULL;

    // Check if user is submitting as a draft.
    $is_draft = (int) !empty($form_state['save_draft']);

    // To maintain time and user information, load the existing submission.
    // If a draft is deleted while a user is working on completing it, $sid will
    // exist, but webform_get_submission() will not find the draft. So, make a new
    // submission.
    if ($sid && $submission = webform_get_submission($node->webform['nid'], $sid)) {
      // Store original data on object for use in update hook.
      $submission->original = clone $submission;

      // Merge with new submission data. The + operator maintains numeric keys.
      // This maintains existing data with just-submitted data when a user resumes
      // a submission previously saved as a draft.
      // Remove any existing data on this and previous pages. If components are hidden, they may
      // be in the $submission->data but absent entirely from $new_data.
      $page_map = webform_get_conditional_sorter($node)->getPageMap();
      for ($page_nr = 1; $page_nr <= $form_state['webform']['page_num']; $page_nr++) {
        $submission->data = array_diff_key($submission->data, $page_map[$page_nr]);
      }
      $submission->data = webform_submission_data($node, $form_state['values']['submitted']) + $submission->data;
    }
    else {
      // Create a new submission object.
      $submission = webform_submission_create($node, $GLOBALS['user'], $form_state);
      // Since this is a new submission, a new sid is needed.
      $sid = NULL;
    }

    // Save draft state, and for drafts, save the current page (if clicking next)
    // or the previous page (if not) as the last valid page.
    $submission->is_draft = $is_draft;
    $submission->highest_valid_page = 0;
    if ($is_draft) {
      $submission->highest_valid_page = end($form_state['clicked_button']['#parents']) == 'next' && $form_state['values']['op'] != '__AUTOSAVE__'
                                            ? $form_state['webform']['page_num']
                                            : $form_state['webform']['page_num'] - 1;
    }
    return new Submission($this, $submission);
  }

  /**
   * Check if webform_confirm_email is active and this submission has to
   * be confirmed.
   *
   * @return bool
   */
  public function needsConfirmation() {
    if (!module_exists('webform_confirm_email')) {
      return FALSE;
    }
    $q = db_select('webform_confirm_email', 'e');
    $q->join('webform_emails', 'we', 'we.nid=e.nid AND we.eid=e.eid');
    $q->fields('e', ['eid'])
      ->condition('e.email_type', 1)
      ->condition('we.nid', $this->node->nid)
      ->condition('we.status', 1);
    return (bool) $q->execute()->fetch();
  }

}
