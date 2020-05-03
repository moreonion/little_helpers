<?php

namespace Drupal\little_helpers\Services;

use Upal\DrupalUnitTestCase;

/**
 * Test the service container.
 */
class ContainerTest extends DrupalUnitTestCase {

  /**
   * Test a string spec.
   */
  public function testStringSpec() {
    $class_a = get_class($this->createMock('stdclass'));
    $container = new Container(['a' => $class_a]);
    $a = $container->loadService('a');
    $this->assertInstanceOf($class_a, $a);
  }

  /**
   * Test a string spec.
   */
  public function testConstructorArgs() {
    $specs['a'] = [
      'class' => \SplFixedArray::class,
      'arguments' => [1],
    ];
    $container = new Container($specs);
    $a = $container->loadService('a');
    $this->assertEqual(1, $a->getSize());
  }

  /**
   * Test a constructor method spec.
   */
  public function testConstructorMethod() {
    $specs['a'] = [
      'class' => \SplFixedArray::class,
      'constructor' => 'fromArray',
      'arguments' => [[1, 2, 3]],
    ];
    $container = new Container($specs);
    $a = $container->loadService('a');
    $this->assertEqual([1, 2, 3], $a->toArray());
  }

  /**
   * Test a constructor method spec.
   */
  public function testCalls() {
    $specs['a'] = [
      'class' => \SplFixedArray::class,
      'arguments' => [2],
      'calls' => [
        ['offsetSet', [0, 1]],
        ['offsetSet', [1, 2]],
      ],
    ];
    $container = new Container($specs);
    $a = $container->loadService('a');
    $this->assertEqual([1, 2], $a->toArray());
  }

  /**
   * Test service resolving in calls.
   */
  public function testServiceResolvingInCalls() {
    $specs['a'] = [
      'class' => \SplFixedArray::class,
      'constructor' => 'fromArray',
      'arguments' => [[1, 2, 3]],
    ];
    $specs['nested_a'] = [
      'class' => \SplFixedArray::class,
      'arguments' => [1],
      'calls' => [
        ['offsetSet', [0, '@a']],
      ],
    ];
    $container = new Container($specs);
    $a = $container->loadService('nested_a');
    $this->assertEqual([\SplFixedArray::fromArray([1, 2, 3])], $a->toArray());
  }

  /**
   * Test loading an unknown service.
   *
   * @expectedException \Drupal\little_helpers\Services\UnknownServiceException
   */
  public function testUnknownServiceException() {
    $container = new Container([]);
    $container->loadService('unknown');
  }

  /**
   * Test loading an unknown service without exception.
   */
  public function testUnknownService() {
    $container = new Container([]);
    $this->assertFalse($container->loadService('unknown', FALSE));
  }

  /**
   * Test injecting an object.
   */
  public function testInjection() {
    $container = new Container([]);
    $container->inject('foo', 'bar');
    $this->assertEqual('bar', $container->loadService('foo'));
  }

  /**
   * Test self registration as service.
   */
  public function testGettingContainerAsService() {
    $container = new Container([]);
    $this->assertSame($container, $container->loadService('container'));
  }

  /**
   * Test getSpec() and instantiate() with keyword arguments.
   */
  public function testLoadWithKwargs() {
    $container = new Container([
      'fixed_array' => [
        'class' => \SplFixedArray::class,
        'constructor' => 'fromArray',
        'arguments' => ['%initial'],
      ],
    ]);
    $a = $container->getSpec('fixed_array')->instantiate(['initial' => [1, 2, 3]]);
    $this->assertEqual([1, 2, 3], $a->toArray());
  }

  /**
   * Test instantiation with parent container.
   */
  public function testSetContainer() {
    $container = new Container([]);
    $container->inject('foo', 'bar');
    $container2 = new Container([
      'queue' => [
        'class' => \SplStack::class,
        'calls' => [
          ['push', ['@foo']],
        ],
      ],
    ]);
    $container2->setContainer($container);
    $q = $container2->loadService('queue');
    $this->assertEqual('bar', $q->top());
  }

}
