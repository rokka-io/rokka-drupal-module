<?php

/**
 * Check if the given uri represents a Rokka uri or not.
 *
 * @param string $uri The URI to check
 *
 * @return bool
 */
function rokka_is_rokka_uri($uri) {
  $scheme = file_uri_scheme($uri);
  if ($scheme === ROKKA_SCHEME) {
    return TRUE;
  }

  return FALSE;
}


/**
 * Check if the Rokka.io service integration is enabled.
 * @return bool
 */
function rokka_service_is_active() {
  // Don't check it the integration service is disabled.
  if (FALSE == variable_get('rokka_service_is_enabled', FALSE)) {
    return FALSE;
  }

  // Check if the current credentials are OK.
  return 200 === rokka_validate_credentials();
}

/**
 * @return \Drupal\rokka\Client
 */
function rokka_get_current_client($reset = FALSE) {
  static $client;
  if ($reset || empty($client)) {
    $conf = \Drupal\rokka\Configuration::fromDrupalVariables();
    $client = new \Drupal\rokka\Client($conf);
  }
  return $client;
}

/**
 * Validate Rokka credentials
 *
 * @return int|null
 *    Returns the HTTP error code associated to the request:
 *      - 200: Ok
 *      - 400: Invalid configuration (InvalidArgumentException)
 *      - 401: Not Authorized
 *      - 403: Forbidden
 */
function rokka_validate_credentials($data = array()) {
  try {
    if (empty($data)) {
      $config = \Drupal\rokka\Configuration::fromDrupalVariables();
    } else {
      $config = \Drupal\rokka\Configuration::fromConfig($data);
    }

    if (\Drupal\rokka\Client::validateConfiguration($config)) {
      return 200;
    }
  } catch (\InvalidArgumentException $e) {
    return 400;
  } catch (\Exception $e) {
    return $e->getCode();
  }
  return NULL;
}
