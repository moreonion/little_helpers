<?php
/**
 * @file
 */

namespace Drupal\little_helpers\Webform;

/**
 * @deprecated
 *   This class is deprecated in favor of Webform::fromFormState().
 */
class FormState {
  protected $node;
  protected $formState;
  public $webform;

  public function __construct($node, $form, array &$form_state) {
    $this->node = $node;
    $this->webform = new Webform($node);
    // Check if webform_client_form_pages() has already been run.
    // Run it on a copy of the form state if not.
    if (!isset($form_state['values']['submitted_tree'])) {
      $this->formState = $form_state;
      webform_client_form_pages($form, $this->formState);
    }
    else {
      $this->formState = &$form_state;
    }
  }

  public function getNode() {
    return $this->node;
  }

  protected function formStateValue(&$component) {
    $form_key = $component['form_key'];
    $cid = $component['cid'];
    // Normally this just works.
    if (isset($this->formState['values']['submitted'][$cid]) == TRUE) {
      return $this->formState['values']['submitted'][$cid];
    }
    elseif (isset($this->formState['values'][$form_key])) {
      return $this->formState['values'][$form_key];
    }
    elseif (isset($this->formState['values']['submitted'][$form_key]) == TRUE) {
      return $this->formState['values']['submitted'][$form_key];
    }
    elseif (isset($this->formState['storage']['submitted'][$cid]) == TRUE) {
      return $this->formState['storage']['submitted'][$cid];
    }
    else {
      return NULL;
    }
  }

  public function valueByCid($cid) {
    if ($component = $this->webform->component($cid)) {
      return $this->formStateValue($component);
    }
  }

  public function valueByKey($form_key) {
    if ($component = $this->webform->componentByKey($form_key)) {
      return $this->formStateValue($component);
    }
  }

  public function valuesByKeys(array $keys) {
    $result = array();
    foreach ($keys as $key) {
      if (($result = $this->valueByKey($key))) {
        $result[$key] = $result;
      }
    }
    return $result;
  }

  public function valuesByType($type) {
    if (empty($this->formState)) {
      return NULL;
    }

    $result = array();
    $components = $this->webform->componentsByType($type);
    foreach ($components as &$component) {
      $result[$component['form_key']] = $this->formStateValue($component);
    }

    return $result;
  }

  public function getSubmission() {
    if (isset($this->formState['values']['details']['sid'])) {
      return Submission::load($this->node->nid, $this->formState['values']['details']['sid']);
    }
  }
}
