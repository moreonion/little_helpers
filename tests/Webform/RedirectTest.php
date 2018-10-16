<?php

namespace Drupal\little_helpers\Webform;

/**
 * Test redirects after a webform submission.
 */
class RedirectTest extends \DrupalUnitTestCase {

  /**
   * Test getting a redirect with an implemented alter-hook.
   */
  public function testGetRedirectWithAlterHook() {
    $submission = (object) [
      'nid' => 1,
      'sid' => 2,
      'data' => [],
    ];
    $node_array['nid'] = 1;
    $node_array['webform'] = [
      'components' => [],
      'redirect_url' => '<confirmation>',
    ];
    $submission = new Submission((object) $node_array, $submission);
    $redirect = $submission->webform->getRedirect($submission);
    unset($redirect[1]['query']['token']);
    $this->assertEqual([
      'node/1/done',
      ['query' => ['test' => 'foo', 'sid' => 2], 'fragment' => 'bar'],
    ], $redirect);
  }

}
