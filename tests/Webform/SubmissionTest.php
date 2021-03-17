<?php

namespace Drupal\little_helpers\Webform;

use Upal\DrupalUnitTestCase;

/**
 * Test webform submission wrapper.
 */
class SubmissionTest extends DrupalUnitTestCase {

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
   * Test get value by key from unknown component.
   */
  public function testGetValueByKeyUnknownComponent() {
    $submission = (object) [
      'data' => [],
    ];
    $node_array['webform'] = ['components' => []];
    $submission = new Submission((object) $node_array, $submission);
    $this->assertNull($submission->valueByKey('something'));
  }

  /**
   * Test that accessing works on submission properties.
   */
  public function testAccessingSubmissionProperties() {
    $submission = (object) [
      'data' => [1 => []],
    ];
    $node_array['webform'] = ['components' => []];
    $submission = new Submission((object) $node_array, $submission);
    $this->assertTrue(isset($submission->data));
    $this->assertTrue(!empty($submission->data));
    $this->assertEquals([1 => []], $submission->data);

    $this->assertFalse(isset($submission->test));
    $submission->test = 1;
    $this->assertEquals(1, $submission->unwrap()->test);
    $this->assertEquals(1, $submission->test);
    $this->assertFalse(empty($submission->test));

    unset($submission->test);
    $this->assertTrue(empty($submission->test));
  }

}
