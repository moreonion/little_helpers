<?php

namespace Drupal\little_helpers\Rest;

use Upal\DrupalUnitTestCase;

/**
 * Test the JSON/REST API-client.
 */
class ClientTest extends DrupalUnitTestCase {

  /**
   * Create a client instance with a stubbed sendRequest() method.
   */
  protected function mockClient(string $endpoint = 'https://example.com', array $options = []) {
    return $this->getMockBuilder(Client::class)
      ->setConstructorArgs([$endpoint, $options])
      ->setMethods(['sendRequest'])
      ->getMock();
  }

  /**
   * Test that send() adds slashes automatically as needed.
   */
  public function testAddSlash() {
    $client = $this->mockClient();
    $client->expects($this->once())->method('sendRequest')
      ->with('https://example.com/no-slash', $this->anything())
      ->willReturn((object) ['data' => '{}']);
    $client->get('no-slash');
  }

  /**
   * Test handling invalid JSON responses.
   */
  public function testInvalidJson() {
    $client = $this->mockClient();
    $client->expects($this->once())->method('sendRequest')
      ->with('https://example.com/', $this->anything())
      ->willReturn((object) ['data' => '{invalid}']);
    $this->expectException(\JsonException::class);
    $client->get('');
  }

}
