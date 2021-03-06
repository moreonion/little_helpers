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
   * Special string arguments:
   * - Arguments starting with '@' are replaced with the service of that name.
   * - Arguments starting with '%' are replaced with a keyword argument passed
   *   add instantiation time.
   *
   * @var array
   */
  protected $arguments;

  /**
   * A sequence of additional calls to be made after instantiating the class.
   *
   * Each item should be an array with two values:
   * - The method to be invoked.
   * - The arguments to be passed to the method. Special arguments work the same
   *   as for $arguments.
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
   *
   * @param array $kwargs
   *   Associative array of keyword arguments that may be passed as arguments.
   */
  public function instantiate(array $kwargs = []) {
    $class = $this->class;
    $arguments = $this->resolveArguments($this->arguments, $kwargs);
    if ($method = $this->constructor) {
      $instance = $class::$method(...$arguments);
    }
    else {
      $instance = new $class(...$arguments);
    }

    foreach ($this->calls as $call) {
      list($method, $arguments) = $call;
      $arguments = $this->resolveArguments($arguments, $kwargs);
      $instance->$method(...$arguments);
    }
    return $instance;
  }

  /**
   * Resolve all special arguments in the argument array.
   */
  protected function resolveArguments(array $spec_args, array $kwargs) {
    $resolvers = [
      '@' => [$this->container, 'loadService'],
      '%' => function ($key) use ($kwargs) {
        if (!array_key_exists($key, $kwargs)) {
          throw new MissingArgumentException("Argument %{$key} was referenced in the spec but was not passed in kwargs.");
        }
        return $kwargs[$key];
      },
      '!' => function ($key) {
        return module_exists('variable') ? variable_get_value($key) : variable_get($key);
      },
    ];
    foreach ($spec_args as &$arg) {
      if (is_string($arg) && $arg && ($resolver = $resolvers[$arg[0]] ?? NULL)) {
        $name = substr($arg, 1);
        $arg = $resolver($name);
      }
    }
    return $spec_args;
  }

}
