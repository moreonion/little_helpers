<?php

namespace Drupal\little_helpers;

/**
 * A collection of helper function for element trees.
 */
class ElementTree {

  /**
   * Apply a callback recursively to all elements in a form or renderable array.
   *
   * The element tree is traversed in depth-first-search pre-order. For example:
   * | root
   * | | fieldset1
   * | | | textfield1
   * | | textfield2
   *
   * This works similar to array_walk_recursive() with two differences:
   * - It uses element_children() to find child elements.
   * - Additional context can be bound to the $callback closure.
   *
   * @param array $element
   *   The root of the tree that should be worked on.
   * @param callable $callback
   *   The function that‘s applied recursively. It must accept two arguments:
   *   - &$element: The reference to the element.
   *   - $key: The element’s key in the parent array or NULL for the root.
   *   - &$parent: The parent element or NULL for the root.
   */
  public static function applyRecursively(array &$element, callable $callback) {
    $stack = [[&$element, NULL, NULL]];
    while ($q = array_pop($stack)) {
      // list() doesn’t work here as it breaks references.
      $callback($q[0], $q[1], $q[2]);
      foreach (array_reverse(element_children($q[0])) as $key) {
        $stack[] = [&$q[0][$key], $key, &$q[0]];
      }
    }
  }

}
