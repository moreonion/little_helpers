<?php

namespace Drupal\little_helpers;

/**
 * A collection of helper function for element trees.
 */
class ElementTree {

  /**
   * Apply a callback recursively to all elements in a form or renderable array.
   *
   * The element tree is traversed using depth-first-search. For example:
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
   * @param bool $post_order
   *   Use a post-order instead of a pre-order traversal.
   */
  public static function applyRecursively(array &$element, callable $callback, $post_order = FALSE) {
    if ($post_order) {
      static::applyRecursivelyPostOrder($element, $callback);
    }
    else {
      static::applyRecursivelyPreOrder($element, $callback);
    }
  }

  /**
   * Apply a callback recursively in pre-order.
   */
  protected static function applyRecursivelyPreOrder(array &$element, callable $callback, &$parent = NULL, $key = NULL) {
    $callback($element, $key, $parent);
    foreach ((element_children($element)) as $child_key) {
      static::applyRecursivelyPreOrder($element[$child_key], $callback, $element, $child_key);
    }
  }

  /**
   * Apply a callback recursively in post-order.
   */
  protected static function applyRecursivelyPostOrder(array &$element, callable $callback, &$parent = NULL, $key = NULL) {
    foreach ((element_children($element)) as $child_key) {
      static::applyRecursivelyPostOrder($element[$child_key], $callback, $element, $child_key);
    }
    $callback($element, $key, $parent);
  }

}
