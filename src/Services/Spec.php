<?php

namespace Drupal\little_helpers\Services;

/**
 * A class instantiation spec.
 */
class Spec {

  /**
   * The class that is to be instantiated.
   *
   * @var string
   */
  protected $class;

  /**
   * Name of a static method that should be invoked for instantiating the class.
   *
   * If this is set to NULL the standard class constructor will be used.
   *
   * @var string|null
   */
  protected $constructor;

  /**
   * Array of arguments that should be passed to the constructor.
   *
   * If an argument is a string starting with '@' the service with the name will
   * be passed instead.
   *
   * @var array
   */
  protected $arguments;

  /**
   * A sequence of additional calls to be made after instantiating the class.
   *
   * Each item should be an array with two values:
   * - The method to be invoked.
   * - The arguments to be passed to the method. String arguments prefixed with
   *   an '@' are resolved to a service of the same name.
   *
   * @var array
   */
  protected $calls;

  /**
   * The container used to resolve services in arguments.
   *
   * @var Drupal\little_helpers\Services\Container
   */
  protected $container;

  /**
   * Create a new instance from a info array or string.
   *
   * @param string|array $info
   *   This can be either an associative array with the following keys:
   *   - class: The class that’s to be instantiated.
   *   - constructor: The constructor method (see the property description).
   *   - arguments: The constructor arguments (see the property description).
   *   - calls: Additional method calls (see the property description).
   *   … or a string. If this is a string it’s value is taken as the class name.
   */
  public static function fromInfo($info) {
    if (!is_array($info)) {
      $info = ['class' => $info];
    }
    $info += [
      'constructor' => NULL,
      'arguments' => [],
      'calls' => [],
    ];
    return new static($info['class'], $info['constructor'], $info['arguments'], $info['calls']);
  }

  /**
   * Construct a new instance.
   *
   * See the property descriptions for more information about the data.
   */
  public function __construct(string $class, string $constructor = NULL, array $arguments = [], array $calls = []) {
    $this->class = $class;
    $this->constructor = $constructor;
    $this->arguments = $arguments;
    $this->calls = $calls;
  }

  /**
   * Set the container.
   */
  public function setContainer(Container $container) {
    $this->container = $container;
  }

  /**
   * Create a new class instance based on the spec.
   */
  public function instantiate() {
    $class = $this->class;
    $arguments = $this->resolveArguments($this->arguments);
    if ($method = $this->constructor) {
      $instance = $class::$method(...$arguments);
    }
    else {
      $instance = new $class(...$arguments);
    }

    foreach ($this->calls as $call) {
      list($method, $arguments) = $call;
      $arguments = $this->resolveArguments($arguments);
      $instance->$method(...$arguments);
    }
    return $instance;
  }

  /**
   * Resolve all special arguments in the argument array.
   */
  protected function resolveArguments(array $spec_args) {
    foreach ($spec_args as &$arg) {
      if (is_string($arg) && $arg[0] == '@') {
        $arg = $this->container->loadService(substr($arg, 1));
      }
    }
    return $spec_args;
  }

}
