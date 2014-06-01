<?php
/**
 * @file
 */

namespace Drupal\little_helpers\Webform;

/**
 * Class for making webform component values before an submission is saved.
 */
class FormState {
  protected $node;
  protected $formState;
  protected $values;
  public $webform;

  public function __construct($node, $form, array &$form_state) {
    $this->node = $node;
    $this->webform = new Webform($node);
    $this->formState = &$form_state;
    // Check if webform_client_form_pages() has already been run.
    // Run it on a copy of the form state if not.
    if (
      strpos($form['#form_id'], 'webform_client_form') === 0
      && isset($form_state['values']['details']['nid'])
      && !isset($form_state['values']['submitted_tree'])
    ) {
      $fs = $form_state;
      webform_client_form_pages($form, $fs);
      $this->values = $fs['values']['submitted'];
    }
    else {
      $this->values = &$form_state['values']['submitted'];
    }
  }

  public function getNode() {
    return $this->node;
  }

  protected function formStateValue(&$component) {
    $form_key = $component['form_key'];
    $cid = $component['cid'];
    // Normally this just works.
    if (isset($this->values[$cid]) == TRUE) {
      return $this->values[$cid];
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
      if (($res = $this->valueByKey($key))) {
        $result[$key] = $res;
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

  public function __sleep() {
    throw new Exception('FormState objects cannot be serialized as they would lose their reference to the form_state.');
  }
}
