<?php

namespace Drupal\little_helpers\Webform;

use Upal\DrupalUnitTestCase;

/**
 * Test redirects after a webform submission.
 */
class RedirectTest extends DrupalUnitTestCase {

  /**
   * Generate a submission stub object.
   */
  protected function submissionStub() {
    $submission = (object) [
      'nid' => 1,
      'sid' => 2,
      'data' => [],
      'submitted' => NULL,
    ];
    $node_array['nid'] = 1;
    $node_array['webform'] = [
      'components' => [],
      'redirect_url' => '<confirmation>',
    ];
    $submission = new Submission((object) $node_array, $submission);
    $submissions = &drupal_static('webform_get_submission', []);
    $submissions[$submission->sid] = $submission;
    return $submission;
  }

  /**
   * Test getting a redirect with an implemented alter-hook.
   */
  public function testGetRedirectWithAlterHook() {
    $submission = $this->submissionStub();
    $redirect = $submission->webform->getRedirect($submission);
    unset($redirect[1]['query']['token']);
    $this->assertEqual([
      'node/1/done',
      ['query' => ['test' => 'foo', 'sid' => 2], 'fragment' => 'bar'],
    ], $redirect);
  }

  /**
   * Test altering after a webform submission.
   */
  public function testAlterRedirectAfterSubmit() {
    $submission = $this->submissionStub();
    $form['#node'] = $submission->node;
    $form_state['values']['details']['sid'] = 2;
    $form_state['redirect'] = 'node/1/done';
    $form_state['webform_completed'] = TRUE;
    _little_helpers_webform_redirect_alter($form, $form_state);
    $this->assertEqual([
      'node/1/done',
      ['query' => ['test' => 'foo'], 'fragment' => 'bar'],
    ], $form_state['redirect']);
  }

  /**
   * Test altering after returning from a confirmation email.
   */
  public function testAlterRedirectAfterConfirmationEmail() {
    $submission = $this->submissionStub();
    $redirect = 'https://example.com?bar=baz#test';
    little_helpers_webform_confirm_email_confirmation_redirect_alter($redirect, $submission->node, $submission);
    $this->assertEqual([
      'https://example.com',
      [
        'query' => ['test' => 'foo', 'bar' => 'baz'],
        'fragment' => 'testbar',
      ],
    ], $redirect);
  }

}
