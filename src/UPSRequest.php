<?php

namespace Drupal\commerce_ups;

/**
 * UPS API Service.
 *
 * @package Drupal\commerce_ups
 */
abstract class UPSRequest implements UPSRequestInterface {
  /**
   * @var array.
   */
  protected $configuration;

  /**
   * Sets configuration for requests.
   *
   * @param $configuration
   */
  public function setConfig($configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Returns authentication array for a request.
   *
   * @return array
   * @throws \Exception
   */
  protected function getAuth() {
    // Verify necessary configuration is available.
    if (empty($this->configuration['api_information']['access_key'])
    || empty($this->configuration['api_information']['user_id'])
    || empty($this->configuration['api_information']['password'])) {
      throw new \Exception('Configuration is required.');
    }

    return [
      'access_key' => $this->configuration['api_information']['access_key'],
      'user_id' => $this->configuration['api_information']['user_id'],
      'password' => $this->configuration['api_information']['password'],
    ];
  }

  /**
   * Determine if integration mode (test or live) should be used.
   *
   * @return boolean
   *   Integration mode (ie: test) is the default.
   */
  public function useIntegrationMode() {
    // If live mode is enabled, do not use integration mode.
    if (!empty($this->configuration['api_information']['mode'])
      && $this->configuration['api_information']['mode'] == 'live') {
      return FALSE;
    }

    // Use integration mode by default.
    return TRUE;
  }
}
