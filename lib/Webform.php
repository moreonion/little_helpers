<?php

namespace Drupal\little_helpers;

class Webform {
  public $node;
  public $nid;

  public function __construct($node) {
    $this->node = $node;
  }

  public static function fromNode(\stdClass $node) {
    return new static($node);
  }

  /**
   * Get the redirect_url for this webform as used by the submit handler.
   *
   * This is mainly a c&p of the relevant parts of
   * @see webform_client_form_submit().
   */
  public function getRedirect($submission = NULL) {
    $node = $this->node;
    $redirect_url = $node->webform['redirect_url'];

    // Clean up the redirect URL and filter it for webform tokens.
    $redirect_url = trim($node->webform['redirect_url']);
    if ($submission) {
      $redirect_url = _webform_filter_values($redirect_url, $node, $submission, NULL, FALSE, TRUE);
    }

    // Remove the domain name from the redirect.
    $redirect_url = preg_replace('/^' . preg_quote($GLOBALS['base_url'], '/') . '\//', '', $redirect_url);

    if ($redirect_url == '<none>') {
      return NULL;
    }
    elseif ($redirect_url == '<confirmation>') {
      $options = array();
      if ($submission) {
        $options['query']['sid'] = $submission->sid;
      }
      return array('node/' . $node->nid . '/done', $options);
    }
    elseif (valid_url($redirect_url, TRUE)) {
      return $redirect_url;
    }
    elseif ($redirect_url && strpos($redirect_url, 'http') !== 0) {
      $parts = drupal_parse_url($redirect_url);
      if ($submission) {
        $parts['query']['sid'] = $submission->sid;
      }
      $query = $parts['query'];
      return array($parts['path'], array('query' => $query, 'fragment' => $parts['fragment']));
    }
    return $redirect_url;
  }

  public function getRedirectUrl($submission = NULL, $absolute = TRUE) {
    $redirect = $this->getRedirect($submission);
    if (is_array($redirect)) {
      $redirect[1]['absolute'] = $absolute;
      return url($redirect[0], $redirect[1]);
    } else {
      return $redirect;
    }
  }

  public function __sleep() {
    $this->nid = $this->node->nid;
    return array('nid');
  }

  public function __wakeup() {
    $this->__construct(\node_load($this->nid));
  }
}
