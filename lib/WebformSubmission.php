<?php

namespace Drupal\little_helpers;

module_load_include('inc', 'webform', 'includes/webform.submissions');

class WebformSubmission {
  protected $node;
  protected $submission;
  protected $webform;

  public $remote_addr;
  public $submitted;

  public static function load($nid, $sid) {
    $node = node_load($nid);
    $submission = webform_get_submission($nid, $sid);
    return new static($node, $submission);
  }

  public function __construct($node, $submission) {
    $this->submission = $submission;
    $this->node    = $node;
    $this->webform = &$node->webform;

    if (!isset($this->webform['cids'])) {
      foreach ($this->webform['components'] as $component) {
        $this->webform['cids'][$component['form_key']] = (int) $component['cid'];
      }
    }

    $this->submitted = $submission->submitted;
    $this->remote_addr = $submission->remote_addr;
  }

  public function getNode() {
    return $this->node;
  }

  public function valueByKey($form_key) {
    if (isset($this->webform['cids'][$form_key])) {
      return $this->submission->data[$this->webform['cids'][$form_key]]['value'][0];
    }
  }

  public function componentByKey($form_key) {
    if (isset($this->webform['cids'][$form_key])) {
      return $this->webform['components'][$this->webform['cids'][$form_key]];
    }
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
