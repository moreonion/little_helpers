<?php

namespace Drupal\little_helpers\Webform;

use Upal\DrupalUnitTestCase;

/**
 * Test CRUD operations for webform submissions.
 */
class SubmissionIntegrationTest extends DrupalUnitTestCase {

  /**
   * Set up a test node and submission.
   */
  public function setUp() : void {
    parent::setUp();
    module_load_include('inc', 'webform', 'includes/webform.submissions');
    $node = (object) ['title' => 'test webform', 'type' => 'webform'];
    node_object_prepare($node);
    $node->webform['components'][1] = [
      'type' => 'email',
      'form_key' => 'email',
      'pid' => 0,
      'name' => 'Email',
      'weight' => 0,
    ];
    node_save($node);
    $this->node = node_load($node->nid);

    $form_state['values']['submitted'][1] = 'test@example.com';
    $this->submission = webform_submission_create($this->node, $GLOBALS['user'], $form_state);
    webform_submission_insert($this->node, $this->submission);
  }

  /**
   * Remove the test node.
   */
  public function tearDown() : void {
    node_delete($this->node->nid);
    parent::tearDown();
  }

  /**
   * Test modifying a submission.
   */
  public function testSaveUpdate() {
    $s = Submission::load($this->node->nid, $this->submission->sid);
    $this->assertTrue((bool) $s->is_draft);
    $s->is_draft = FALSE;
    $s->save();
    $this->assertFalse((bool) $s->is_draft);
    // Reset the static cache otherwise we don’t get the data from the database.
    drupal_static_reset('webform_get_submission');
    $s = Submission::load($s->node->nid, $s->sid);
    $this->assertFalse((bool) $s->is_draft);
  }

  /**
   * Test deleting a submission.
   */
  public function testDelete() {
    $s = new Submission($this->node, $this->submission);
    $s->delete();
    $this->assertEmpty(Submission::load($s->node->nid, $s->sid));
  }

  /**
   * Test whether hook_webform_submission_confirmed() is called.
   */
  public function testConfirmedHook() {
    // The submission has been saved as draft so the hook shouldn’t be called.
    $this->assertFalse(!empty($this->submission->confirmed_hook_called));

    $this->submission->is_draft = FALSE;
    webform_submission_update($this->node, $this->submission);
    // First save as non-draft should trigger the confirmed hook.
    $this->assertTrue(!empty($this->submission->confirmed_hook_called));

    $this->submission->confirmed_hook_called = FALSE;
    webform_submission_update($this->node, $this->submission);
    // Saving the submission again shouldn’t called the hook.
    $this->assertFalse(!empty($this->submission->confirmed_hook_called));
  }

}
