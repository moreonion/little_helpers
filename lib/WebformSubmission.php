<?php

namespace Drupal\little_helpers;

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

  public function valueByKey($form_key) {
    if (isset($this->webform['cids'][$form_key])) {
      return $this->submission->data[$this->webform['cids'][$form_key]]['value'][0];
    }
  }
}
