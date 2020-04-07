<?php

/**
 * @file
 * Document hooks invoked by the little_helpers module.
 */

/**
 * Get a list of service specifications.
 *
 * @return array
 *   Service specifications keyed by the service name.
 *
 * @see \Drupal\little_helpers\Services\Spec
 */
function hook_little_helpers_services() {
  $info['a'] = '\\SplFixedArray';
  $info['b'] = [
    'class' => '\\SplQueue',
    'calls' => [
      ['push', [1]],
      ['push', ['@a']],
    ],
  ];
  return $info;
}

/**
 * Alter the service specs.
 *
 * @param array $info
 *   The array of specs as defined by the modules.
 */
function hook_little_helpers_services_alter(array &$info) {
  $info['b']['class'] = '\\SplStack';
}
