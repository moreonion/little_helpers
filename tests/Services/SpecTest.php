<?php

namespace Drupal\little_helpers\Services;

use Upal\DrupalUnitTestCase;

/**
 * Test the instantiating spec class.
 */
class SpecTest extends DrupalUnitTestCase {

  /**
   * Test passing keyword arguments in the spec.
   */
  public function testKwargs() {
    $spec = Spec::fromInfo([
      'class' => \SplFixedArray::class,
      'constructor' => 'fromArray',
      'arguments' => ['%initial'],
      'calls' => [
        ['offsetSet', [0, '%other']],
      ],
    ]);
    $this->assertEqual([0, 2, 3], $spec->instantiate([
      'initial' => [1, 2, 3],
      'other' => 0,
    ])->toArray());
  }

  /**
   * Test exception when keyword argument is not defined.
   *
   * @expectedException \Drupal\little_helpers\Services\MissingArgumentException
   */
  public function testKwargsException() {
    $spec = Spec::fromInfo([
      'class' => \SplFixedArray::class,
      'constructor' => 'fromArray',
      'arguments' => ['%initial'],
    ]);
    $spec->instantiate();
  }

  /**
   * Test that Spec::fromInfo() can handle class-only string specs.
   */
  public function testFromInfoHandlesStrings() {
    $a = Spec::fromInfo(\SplFixedArray::class)->instantiate();
    $this->assertInstanceOf(\SplFixedArray::class, $a);
  }

}
