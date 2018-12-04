<?php

namespace Drupal\little_helpers;

/**
 * Helper functions to manage configuration stored in arrays.
 */
class ArrayConfig {

  /**
   * Check whether an array is mergable.
   *
   * @param array $value
   *   The array to check.
   *
   * @return bool
   *   TRUE if the array is mergable, otherwise FALSE.
   */
  public static function isMergable(array $value) {
    // Short cut for empty arrays.
    if (!$value) {
      return FALSE;
    }
    // Numeric arrays are not mergable.
    if (array_keys($value) === range(0, count($value) - 1)) {
      return FALSE;
    }
    // Arrays that look like checkbox values are not mergable.
    foreach ($value as $k => $v) {
      if ($v !== 0 && $k != $v) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Helper functions that recursively merges $defaults into a $config array.
   *
   * In general it tries to do the right thing: Associative arrays are merged
   * except when they look like checkbox values.
   *
   * @param array &$config
   *   A config array to merge the defaults into.
   * @param array $defaults
   *   The defaults array.
   */
  public static function mergeDefaults(array &$config, array $defaults) {
    $config += $defaults;
    foreach ($config as $key => $value) {
      if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])) {
        if ((!$value && static::isMergable($defaults[$key])) || static::isMergable($value)) {
          self::mergeDefaults($config[$key], $defaults[$key]);
        }
      }
    }
  }

}
