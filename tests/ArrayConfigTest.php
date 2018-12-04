<?php

namespace Drupal\little_helpers;

use Upal\DrupalUnitTestCase;

/**
 * Unit-tests for the array config helper class.
 */
class ArrayConfigTest extends DrupalUnitTestCase {

  /**
   * Test various different kinds of config default configurations.
   */
  public function testMergeDefaults() {
    $config = [
      'top-level' => 1,
      'empty_assoc' => [],
      'empty_numeric' => [],
      'empty_select_config' => [],
      'assoc' => ['a' => 1],
      'numeric' => [1, 2, 3],
      'select_config' => ['a' => 'a', 'b' => 0],
    ];
    $defaults = [
      'top-level' => 2,
      'empty_assoc' => ['foo' => 'bar'],
      'empty_numeric' => [1, 2, 3],
      'empty_select_config' => ['a' => 'a', 'b' => 0],
      'assoc' => ['b' => 2],
      'numeric' => [4, 5, 6],
      'select_config' => ['b' => 'b', 'c' => 'c'],
    ];
    ArrayConfig::mergeDefaults($config, $defaults);
    $this->assertEquals([
      'top-level' => 1,
      'empty_assoc' => ['foo' => 'bar'],
      'empty_numeric' => [],
      'empty_select_config' => [],
      'assoc' => ['a' => 1, 'b' => 2],
      'numeric' => [1, 2, 3],
      'select_config' => ['a' => 'a', 'b' => 0],
    ], $config);
  }

  /**
   * Test simple associatve arrays are mergable.
   */
  public function testMergableAssociativaArray() {
    $this->assertTrue(ArrayConfig::isMergable(['foo' => 'bar']));
  }

  /**
   * Test that arrays that look like checkbox values are not mergable.
   */
  public function testMergableCheckboxValues() {
    $this->assertFalse(ArrayConfig::isMergable(['a' => 'a', 'b' => 0]));
  }

  /**
   * Test that numeric arrays are not mergable.
   */
  public function testMergableNumericArray() {
    $this->assertFalse(ArrayConfig::isMergable(['a', 1, []]));
  }

  /**
   * Test that mixed arrays are mergable.
   */
  public function testMergableMixedArray() {
    $this->assertTrue(ArrayConfig::isMergable(['a', 'b' => 'foo', []]));
  }

}
