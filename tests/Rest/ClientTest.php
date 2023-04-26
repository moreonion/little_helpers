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

  /**
   * Test decoding gzip compressed data.
   */
  public function testGzipResponse() {
    $client = $this->mockClient();
    $response = (object) [
      'data' => gzencode('{"foo": 42}'),
      'headers' => ['content-encoding' => 'gzip'],
    ];
    $has_accept_encoding = function ($options) {
      return $options['headers']['Accept-Encoding'] === 'deflate, gzip';
    };
    $client->expects($this->once())->method('sendRequest')
      ->with('https://example.com/', self::callback($has_accept_encoding))
      ->willReturn($response);
    $result = $client->get('');
    $this->assertEqual(["foo" => 42], $result);
  }

  /**
   * Test decoding deflate compressed data.
   */
  public function testDeflateResponse() {
    $client = $this->mockClient();
    $response = (object) [
      'data' => gzdeflate('{"foo": 42}'),
      'headers' => ['content-encoding' => 'deflate'],
    ];
    $client->expects($this->once())->method('sendRequest')
      ->with('https://example.com/', $this->anything())
      ->willReturn($response);
    $result = $client->get('');
    $this->assertEqual(["foo" => 42], $result);
  }

  /**
   * Test response without data.
   */
  public function testEmptyResponse() {
    $client = $this->mockClient();
    $client->expects($this->once())->method('sendRequest')
      ->with('https://example.com/', $this->anything())
      ->willReturn((object) ['data' => '']);
    $this->assertEmpty($client->get(''));
  }

}
