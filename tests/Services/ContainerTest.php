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
    $specs['a'] = $class_a;
    $container = new Container();
    $container->setSpecs($specs);
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
    $container = new Container();
    $container->setSpecs($specs);
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
    $container = new Container();
    $container->setSpecs($specs);
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
    $container = new Container();
    $container->setSpecs($specs);
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
    $container = new Container();
    $container->setSpecs($specs);
    $a = $container->loadService('nested_a');
    $this->assertEqual([\SplFixedArray::fromArray([1, 2, 3])], $a->toArray());
  }

  /**
   * Test loading an unknown service.
   */
  public function testUnknownServiceException() {
    $container = new Container();
    $this->expectException(UnknownServiceException::class);
    $container->loadService('unknown');
  }

  /**
   * Test loading an unknown service without exception.
   */
  public function testUnknownService() {
    $container = new Container();
    $this->assertFalse($container->loadService('unknown', FALSE));
  }

  /**
   * Test injecting an object.
   */
  public function testInjection() {
    $container = new Container();
    $container->inject('foo', 'bar');
    $this->assertEqual('bar', $container->loadService('foo'));
  }

  /**
   * Test self registration as service.
   */
  public function testGettingContainerAsService() {
    $container = new Container();
    $this->assertSame($container, $container->loadService('container'));
  }

  /**
   * Test getSpec() and instantiate() with keyword arguments.
   */
  public function testLoadWithKwargs() {
    $container = new Container();
    $container->setSpecs([
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
   * Test passing config variables as arguments.
   */
  public function testLoadWithVariables() {
    $container = new Container();
    $container->setSpecs([
      'fixed_array' => [
        'class' => \SplFixedArray::class,
        'constructor' => 'fromArray',
        'arguments' => ['!initial'],
      ],
    ]);
    $GLOBALS['conf']['initial'] = [1, 2, 3];
    $a = $container->getSpec('fixed_array')->instantiate();
    $this->assertEqual([1, 2, 3], $a->toArray());
  }

  /**
   * Test instantiation with parent container.
   */
  public function testSetContainer() {
    $container = new Container();
    $container->inject('foo', 'bar');
    $container2 = new Container();
    $container2->setSpecs([
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

  /**
   * Test that new specs overwrite old specs.
   */
  public function testSetSpecsOverwrites() {
    $container = new Container();
    $specs1['x'] = [
      'class' => \SplStack::class,
      'calls' => [
        ['push', ['foo']],
      ],
    ];
    $specs1['y'] = [
      'class' => \SplStack::class,
      'calls' => [
        ['push', ['baz']],
      ],
    ];
    $container->setSpecs($specs1);
    $specs2['x'] = [
      'class' => \SplStack::class,
      'calls' => [
        ['push', ['bar']],
      ],
    ];
    $container->setSpecs($specs2);
    $this->assertEqual('bar', $container->loadService('x')->top());
    $this->assertEqual('baz', $container->loadService('y')->top());
  }

  /**
   * Test spec defaults.
   */
  public function testDefaults() {
    $defaults = [
      'class' => \SplStack::class,
      'calls' => [['push', ['bar']]],
    ];
    $container = new Container();
    $container->setDefaults($defaults);
    $container->setSpecs(['queue' => []]);
    $q = $container->loadService('queue');
    $this->assertEqual('bar', $q->top());
  }

}
