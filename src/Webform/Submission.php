<?php

namespace Drupal\little_helpers\Webform;

module_load_include('inc', 'webform', 'includes/webform.submissions');

/**
 * A useful wrapper for webform submission objects.
 */
class Submission {

  public $node;
  protected $submission;
  public $webform;

  /**
   * Load a submission object based on it's $nid and $sid.
   *
   * @param int $nid
   *   Node ID of the submission.
   * @param int $sid
   *   Submission ID.
   * @param bool $reset
   *   Whether to reset the static cache from webform_get_submission(). Pass
   *   this if you are batch-processing submissions.
   *
   * @return \Drupal\little_helpers\Webform\Submission
   *   The submission or NULL if the no submission could be loaded.
   */
  public static function load($nid, $sid, $reset = FALSE) {
    // Neither node_load() nor webform_get_submission() can handle invalid IDs.
    if (!$nid || !$sid) {
      return NULL;
    }
    $node = node_load($nid);
    $submission = webform_get_submission($nid, $sid, $reset);
    if ($node && $submission) {
      return new static($node, $submission);
    }
  }

  /**
   * Constructor.
   *
   * @param object $node_or_webform
   *   Either a node-object or a Webform instance.
   * @param object $submission
   *   A submission object as created by webform.
   */
  public function __construct($node_or_webform, $submission) {
    $this->submission = $submission;
    if ($node_or_webform instanceof Webform) {
      $this->node = $node_or_webform->node;
      $this->webform = $node_or_webform;
    }
    else {
      $this->node = $node_or_webform;
      $this->webform = new Webform($node_or_webform);
    }

    if (!isset($submission->tracking)) {
      $submission->tracking = (object) [];
    }
  }

  /**
   * Retrieve a single value by a component's form_key.
   *
   * @param string $form_key
   *   The form_key to look for.
   *
   * @return mixed
   *   A value if possible or NULL otherwise.
   */
  public function valueByKey($form_key) {
    if ($values = $this->valuesByKey($form_key)) {
      return reset($values);
    }
    return NULL;
  }

  /**
   * Retrieve all values for a component by it's form_key.
   *
   * @param string $form_key
   *   The form_key to look for.
   *
   * @return array
   *   An array of values.
   */
  public function valuesByKey($form_key) {
    if ($component = &$this->webform->componentByKey($form_key)) {
      return $this->valuesByCid($component['cid']);
    }
    elseif (isset($this->submission->tracking->$form_key)) {
      return [$this->submission->tracking->$form_key];
    }
    return [];
  }

  /**
   * Get values for all components of a type.
   *
   * @param string $type
   *   The webform component type.
   *
   * @return array
   *   Values keyed by component ID.
   */
  public function valuesByType($type) {
    $values = array();
    foreach (array_keys($this->webform->componentsByType($type)) as $cid) {
      $values[$cid] = $this->valueByCid($cid);
    }
    return $values;
  }

  /**
   * Get one value for a given component.
   *
   * @param int $cid
   *   The component ID.
   *
   * @return mixed
   *   The value of the component or NULL if there is no value.
   */
  public function valueByCid($cid) {
    if ($values = $this->valuesByCid($cid)) {
      return reset($values);
    }
    return NULL;
  }

  /**
   * Get all values for a component.
   *
   * @param int $cid
   *   The component ID.
   *
   * @return array
   *   An array of component values.
   */
  public function valuesByCid($cid) {
    if (isset($this->submission->data[$cid])) {
      return $this->submission->data[$cid];
    }
    return [];
  }

  /**
   * Get the original webform object.
   */
  public function unwrap() {
    return $this->submission;
  }

  /**
   * Return the nid and sid in an array.
   *
   * @return int[]
   *   Array with two keys:
   *   - nid: The nid of the webform.
   *   - sid: The sid of the submission.
   */
  public function ids() {
    return array(
      'nid' => $this->node->nid,
      'sid' => $this->submission->sid,
    );
  }

  /**
   * Transparently access submission properties.
   *
   * @return mixed
   *   The value of the submission property.
   */
  public function __get($name) {
    return $this->submission->$name;
  }

  /**
   * Get the node of the submission.
   *
   * @return object
   *   The node.
   */
  public function getNode() {
    return $this->node;
  }

}
