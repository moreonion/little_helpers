<?php

namespace Drupal\little_helpers;

use Upal\DrupalUnitTestCase;

/**
 * Test the element tree utility functions.
 */
class ElementTreeTest extends DrupalUnitTestCase {

  /**
   * Test reading and modifying the element tree.
   */
  public function testApplyRecursivelyPreOrder() {
    $test_array = [
      'a' => [
        'a1' => [],
      ],
      'b' => [],
      '#no-element' => 'test',
    ];
    $element_keys = [];
    $count = 0;
    ElementTree::applyRecursively($test_array, function (&$element, $key) use (&$element_keys, &$count) {
      if ($key) {
        $element_keys[] = $key;
      }
      $element['#index'] = $count++;
    });
    $this->assertEqual(['a', 'a1', 'b'], $element_keys);
    $this->assertEqual([
      'a' => [
        'a1' => ['#index' => 2],
        '#index' => 1,
      ],
      'b' => ['#index' => 3],
      '#no-element' => 'test',
      '#index' => 0,
    ], $test_array);
  }

  /**
   * Test post-order traversal.
   */
  public function testApplyRecursivelyPostOrder() {
    $test_array = [
      'a' => [
        'a1' => ['#add' => [1, 2]],
        '#add' => [3],
      ],
      'b' => ['#add' => [4]],
    ];
    ElementTree::applyRecursively($test_array, function (&$element, $key, &$parent) {
      $element += [
        '#sum' => 0,
        '#add' => [],
      ];
    });
    ElementTree::applyRecursively($test_array, function (&$element, $key, &$parent) {
      $element['#sum'] += array_sum($element['#add']);
      if ($parent) {
        $parent['#sum'] += $element['#sum'];
      }
    }, TRUE);
    $this->assertEqual(3, $test_array['a']['a1']['#sum']);
    $this->assertEqual(6, $test_array['a']['#sum']);
    $this->assertEqual(10, $test_array['#sum']);
  }

}
