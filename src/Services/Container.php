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
   * Container used to resolve service references in specs.
   *
   * @var \Drupal\little_helpers\Services\Container
   */
  protected $container = NULL;

  /**
   * Create or get the singleton container instance.
   */
  public static function get() {
    $instance = &drupal_static(__CLASS__);
    if (!$instance) {
      $instance = new static();
      $instance->loadSpecsFromHook('little_helpers_services');
    }
    return $instance;
  }

  /**
   * Create a new loader instance.
   */
  public function __construct($specs = [], $name = 'container') {
    $this->instances[$name] = $this;
    $this->specs = $specs;
  }

  /**
   * Load a (possibly cached) service by name.
   *
   * @param string $name
   *   Name of the service to load.
   * @param bool $exception
   *   Whether to throw an exception if the service can’t be loaded. If FALSE
   *   then a boolean FALSE will be returned instead.
   */
  public function loadService(string $name, bool $exception = TRUE) {
    if ($service = $this->instances[$name] ?? NULL) {
      return $service;
    }
    if ($spec = $this->getSpec($name, $exception)) {
      return $this->instances[$name] = $spec->instantiate();
    }
    return FALSE;
  }

  /**
   * Load specs by invoking a hook.
   *
   * @param string $hook
   *   Name of the hook to invoke.
   * @param mixed ...$arguments
   *   Arguments that should be passed to the hook invocations.
   */
  public function loadSpecsFromHook(string $hook, ...$arguments) {
    $specs = module_invoke_all($hook, ...$arguments);
    foreach ($specs as &$spec) {
      if (!is_array($spec)) {
        $spec = ['class' => $spec];
      }
    }
    drupal_alter($hook, $specs, ...$arguments);
    $this->specs += $specs;
  }

  /**
   * Set the container used to resolve service references in specs.
   *
   * @param \Drupal\little_helper\Services\Container
   *   The container instance to set.
   */
  public function setContainer(Container $container) {
    $this->container = $container;
  }

  /**
   * Get a spec to for creating a new instance of the referenced class.
   *
   * @param string $name
   *   Name of the spec to be loaded.
   * @param bool $exception
   *   Whether to throw an exception if no spec with the name exists. If FALSE
   *   then a boolean FALSE will be returned instead.
   *
   * @return \Drupal\little_helpers\Services\Spec
   *   The registered spec for the $name.
   */
  public function getSpec(string $name, bool $exception = TRUE) {
    if ($spec = $this->specs[$name] ?? NULL) {
      $spec = Spec::fromInfo($spec);
      $spec->setContainer($this->container ?? $this);
      return $spec;
    }
    if ($exception) {
      throw new UnknownServiceException("Unknown service: $name");
    }
    return FALSE;
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
