<?php

namespace Drupal\little_helpers\Webform;

module_load_include('inc', 'webform', 'includes/webform.submissions');

class Submission {
  protected $node;
  protected $submission;
  public $webform;

  public $remote_addr;
  public $submitted;

  public static function load($nid, $sid) {
    $node = node_load($nid);
    $submission = webform_get_submission($nid, $sid);
    return new static($node, $submission);
  }

  /**
   * Construct a submission object from a form_state like
   * @see webform_client_form_submit() does.
   */
  public static function fromFormState($node, &$form_state) {
    $is_draft = (int) !empty($form_state['save_draft']);
    $sid = $form_state['values']['details']['sid'] ? (int) $form_state['values']['details']['sid'] : NULL;
    $data = webform_submission_data($node, $form_state['values']['submitted']);

    if (!$sid) {
      $submission = new static($node, (object) array(
        'nid' => $node->nid,
        'uid' => $form_state['values']['details']['uid'],
        'submitted' => REQUEST_TIME,
        'remote_addr' => ip_address(),
        'is_draft' => $is_draft,
        'data' => $data,
      ));
    }
    else {
      $submission = static::load($node->webform['nid'], $sid);
      $submission->is_draft = $is_draft;
      $submission->data = $data + $submission->data;
    }
    return $submission;
  }

  public function __construct($node, $submission) {
    $this->submission = $submission;
    $this->node    = $node;
    $this->webform = new Webform($node);

    $this->submitted = $submission->submitted;
    $this->remote_addr = $submission->remote_addr;

    if (!isset($submission->tracking)) {
      $submission->tracking = array();
      if (module_exists('webform_tracking')) {
        webform_tracking_load($submission);
      }
    }
  }

  public function getNode() {
    return $this->node;
  }

  public function valueByKey($form_key) {
    if ($component = &$this->webform->componentByKey($form_key)) {
      return $this->submission->data[$component['cid']]['value'][0];
    }
    elseif (isset($this->submission->tracking->$form_key)) {
      return $this->submission->tracking->$form_key;
    }
  }

  public function valuesByKey($form_key) {
    if ($component = &$this->webform->componentByKey($form_key)) {
      return $this->submission->data[$component['cid']]['value'];
    }
    elseif (isset($this->submission->tracking[$form_key])) {
      return $this->submission->tracking[$form_key];
    }
  } 

  public function valuesByType($type) {
    $values = array();
    foreach (array_keys($this->componentsByType($type)) as $cid) {
      $values[$cid] = $this->submission->data[$cid]['value'][0];
    }
    return $values;
  }

  public function valueByCid($cid) {
    return $this->submission->data[$cid]['value'][0];
  }

  public function valuesByCid($cid) {
    return $this->submission->data[$cid]['value'];
  }

  public function unwrap() {
    return $this->submission;
  }

  public function __sleep() {
    $this->nid = $this->node->nid;
    $this->sid = $this->submission->sid;
    return array('nid', 'sid');
  }

  public function __wakeup() {
    $this->__construct(node_load($this->nid), webform_get_submission($this->nid, $this->sid));
  }
}
