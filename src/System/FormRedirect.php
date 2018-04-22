<?php

namespace Drupal\little_helpers\System;

/**
 * Standardized data-structure for form redirects.
 *
 * Form redirects can be either a string URL or a numeric array with the two
 * arguments for `url()`. This class encapsulates this data-structure into
 * something that can be handled more easily.
 */
class FormRedirect {

  public $path;
  public $query = [];
  public $fragment = '';

  /**
   * Create redirect object from an array of options.
   */
  public function __construct($data) {
    foreach ($data as $k => $v) {
      $this->{$k} = $v;
    }
  }

  /**
   * Create a redirect based on a $form_state['redirect'].
   *
   * @param mixed $redirect
   *   Either a string URL or an array with up to two elements:
   *   - The path.
   *   - Additional options for the `url()` function.
   */
  public static function fromFormStateRedirect($redirect) {
    if (!$redirect) {
      $data = ['path' => NULL];
    }
    elseif (is_array($redirect)) {
      $data = ['path' => $redirect[0]];
      if (isset($redirect[1])) {
        $data += $redirect[1];
      }
    }
    else {
      $data = drupal_parse_url($redirect);
    }
    return new static($data);
  }

  /**
   * Generate a $form_state['redirect'] compatible version of this redirect.
   *
   * @return null|array
   *   Array with two elements:
   *   1. The path.
   *   2. Additional options.
   *   … or NULL if the path is NULL.
   */
  public function toFormStateRedirect() {
    if (is_null($this->path)) {
      return NULL;
    }
    $options = (array) $this;
    unset($options['path']);
    return [$this->path, $options];
  }

}
