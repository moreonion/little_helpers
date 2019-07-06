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
    $bfs_order = [];
    $count = 0;
    ElementTree::applyRecursively($test_array, function (&$element, $key) use (&$bfs, &$count) {
      if ($key) {
        $bfs[] = $key;
      }
      $element['#index'] = $count++;
    });
    $this->assertEqual(['a', 'a1', 'b'], $bfs);
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

}
