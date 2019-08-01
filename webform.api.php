<?php

use Drupal\little_helpers\System\FormRedirect;
use Drupal\little_helpers\Webform\Submission;

/**
 * React when a webform submission is completed and email confirmation is done.
 *
 * This hook is invoked in two cases:
 * - A submission that doesn't need confirmation is saved as complete for the
 *   its first time.
 * - A submission needing email confirmation is confirmed.
 *
 * @param \Drupal\little_helpers\Webform\Submission $submission
 *  The submission just having been confirmed / saved.
 */

function hook_webform_submission_confirmed(Submission $submission) {
}

/**
 * Alter the URL the user is redirected to after submitting a webform.
 *
 * @param \Drupal\little_helpers\System\FormRedirect $redirect
 *   The redirect to be altered.
 * @param \Drupal\little_helpers\Webform\Submission $submission
 *   The submission that is about to be finished.
 */
function hook_webform_redirect_alter(FormRedirect &$redirect, Submission $submission) {
  $redirect->query['utm_source'] = 'my-tracking-parameter';
}
