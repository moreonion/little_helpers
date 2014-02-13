<?php
/**
 * @file
 */

namespace Drupal\little_helpers\Webform;

class FormState {
  protected $node;
  protected $formState;

  public function __construct($node, array &$form_state) {
    $this->node = $node;
    $this->webform = new Webform($node);
    $this->formState = &$form_state;
  }

  protected function formStateValue(&$component) {
    $form_key = $component['form_key'];
    $cid = $component['cid'];
    if (isset($this->formState['values'][$form_key])) {
      return $this->formState['values'][$form_key];
    }
    elseif (isset($this->formState['values']['submitted'][$cid]) == TRUE) {
      return $this->formState['values']['submitted'][$cid];
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
      return $this->getFormStateValue($component);
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
