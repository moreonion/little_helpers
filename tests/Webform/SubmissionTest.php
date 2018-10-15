<?php

namespace Drupal\little_helpers\Webform;

/**
 * Test webform submission wrapper.
 */
class SubmissionTest extends \DrupalUnitTestCase {

  /**
   * Test getting values for a component.
   */
  public function testGetValueByKeyFromComponent() {
    $components[1] = [
      'cid' => 1,
      'type' => 'textfield',
      'form_key' => 'text',
      'page_num' => 1,
    ];
    $data[1] = ['text'];
    $submission = (object) ['data' => $data];
    $node_array['webform'] = ['components' => $components];
    $submission = new Submission((object) $node_array, $submission);
    $this->assertEqual(['text'], $submission->valuesByKey('text'));
    $this->assertEqual('text', $submission->valueByKey('text'));
  }

  /**
   * Test getting values from tracking data.
   */
  public function testGetValueByKeyFromTracking() {
    $submission = (object) [
      'data' => [],
      'tracking' => (object) ['country' => 'ZZ'],
    ];
    $node_array['webform'] = ['components' => []];
    $submission = new Submission((object) $node_array, $submission);
    $this->assertEqual(['ZZ'], $submission->valuesByKey('country'));
    $this->assertEqual('ZZ', $submission->valueByKey('country'));
  }

}
