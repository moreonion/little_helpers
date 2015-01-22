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
    if ($node && $submission) {
      return new static($node, $submission);
    }
  }

  public function __construct($node, $submission) {
    $this->submission  = $submission;
    $this->node        = $node;
    $this->webform     = new Webform($node);
    $this->submitted   = $submission->submitted;
    $this->remote_addr = $submission->remote_addr;

    if (!isset($submission->tracking)) {
      $submission->tracking = (object) array();
      if (module_exists('webform_tracking') && isset($submission->sid)) {
        webform_tracking_load($submission);
      }
    }
    // Some components like checkboxes and fieldsets may have no values
    // We want to return NULL in that case instead of throwing a notice.
    foreach (array_keys($this->node->webform['components']) as $cid) {
      if (!isset($this->submission->data[$cid])) {
        $this->submission->data[$cid]['value'] = array(NULL);
      }
    }
  }

  public function getNode() {
    return $this->node;
  }

  public function valueByKey($form_key) {
    if ($component = &$this->webform->componentByKey($form_key)) {
      return $this->valueByCid($component['cid']);
    }
    elseif (isset($this->submission->tracking->$form_key)) {
      return $this->submission->tracking->$form_key;
    }
  }

  public function valuesByKey($form_key) {
    if ($component = &$this->webform->componentByKey($form_key)) {
      return $this->valuesByCid($component['cid']);
    }
    elseif (isset($this->submission->tracking->$form_key)) {
      return $this->submission->tracking->$form_key;
    }
  }

  public function valuesByType($type) {
    $values = array();
    foreach (array_keys($this->componentsByType($type)) as $cid) {
      $values[$cid] = $this->valueByCid($cid);
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

  public function ids() {
    return array(
      'nid' => $this->node->nid,
      'sid' => $this->submission->sid,
    );
  }

  /**
   * @deprecated Serializing submission objects is not a good idea especially
   *   for long term storage.
   */
  public function __sleep() {
    $this->nid = $this->node->nid;
    $this->sid = $this->submission->sid;
    return array('nid', 'sid');
  }

  /**
   * @deprecated Serializing submission objects is not a good idea especially
   *   for long term storage.
   */
  public function __wakeup() {
    if (!($node = node_load($this->nid))) {
      throw new \UnexpectedValueException('Tried to __wakeup with non-existing node.');
    }
    if (!($submission = webform_get_submission($this->nid, $this->sid))) {
      throw new \UnexpectedValueException('Tried to __wakeup with non-existing submission.');
    }
    $this->__construct($node, $submission);
  }
}
