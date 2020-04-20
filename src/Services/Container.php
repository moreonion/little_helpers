<?php

namespace Drupal\little_helpers\Services;

/**
 * Dependency injection container.
 *
 * The main purpose of this class is to serve as a dependency injection
 * container and service registry. It also provides a method for instantiating
 * classes based on DI specs, which is useful for plugin mechanisms.
 */
class Container {

  /**
   * Service specifications.
   *
   * @var array
   */
  protected $specs = [];

  /**
   * Cached instances of services.
   *
   * @var array
   */
  protected $instances = [];

  /**
   * Create or get the singleton container instance.
   */
  public static function get() {
    $instance = &drupal_static(__CLASS__);
    if (!$instance) {
      $instance = static::fromInfo();
    }
    return $instance;
  }

  /**
   * Get specs by invoking the hooks then create a new instance.
   */
  public static function fromInfo() {
    $specs = module_invoke_all('little_helpers_services');
    drupal_alter('little_helpers_services', $specs);
    return new static($specs);
  }

  /**
   * Create a new loader instance.
   */
  public function __construct($specs = [], $name = 'container') {
    $this->instances[$name] = $this;
    $this->specs = $specs;
  }

  /**
   * Load a service by name.
   *
   * @param string $name
   *   Name of the service to load.
   * @param bool $exception
   *   Whether to throw an exception if the service can’t be loaded. If FALSE
   *   then a boolean FALSE will be returned instead.
   */
  public function loadService($name, $exception = TRUE) {
    if ($service = $this->instances[$name] ?? NULL) {
      return $service;
    }
    if ($spec = $this->specs[$name] ?? NULL) {
      return $this->instances[$name] = $this->loadFromSpec($spec);
    }
    if ($exception) {
      throw new UnknownServiceException("Unknown service: $name");
    }
    return FALSE;
  }

  /**
   * Resolve all service-like strings in an array of arguments.
   */
  public function resolveServices(array $args) {
    foreach ($args as &$arg) {
      if (is_string($arg) && $arg[0] == '@') {
        $arg = $this->loadService(substr($arg, 1));
      }
    }
    return $args;
  }

  /**
   * Create a new instance of a class based on an array specification.
   *
   * @param mixed $spec
   *   A spec can either be a fully qualified class name or an array with at
   *   least one member 'class' which must be a fully qualified class name.
   *
   * @return mixed
   *   A class instance created as described in the spec.
   */
  public function loadFromSpec($spec) {
    if (!($spec instanceof Spec)) {
      $spec = Spec::fromInfo($spec);
    }
    $spec->setContainer($this);
    return $spec->instantiate();
  }

  /**
   * Manually register an object as a service.
   *
   * This is mainly supposed to be used for testing.
   */
  public function inject($name, $instance) {
    $this->instances[$name] = $instance;
  }

}
