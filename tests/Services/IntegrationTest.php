<?php

namespace Drupal\little_helpers\Services;

use Upal\DrupalUnitTestCase;

/**
 * Interation tests for the global container instance.
 */
class IntegrationTest extends DrupalUnitTestCase {

  /**
   * Test whether we can get a service defined in a hook.
   */
  public function testGetTestService() {
    $loader = Container::get()->loadService('little_helpers_test.loader');
    $this->assertNotEmpty($loader);
    $this->assertInstanceOf(Container::class, $loader);
  }

}
