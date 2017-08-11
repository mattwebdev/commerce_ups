<?php

namespace Drupal\commerce_ups;

/**
 * UPS API Service.
 *
 * @package Drupal\commerce_ups
 */
class UPSRequest implements UPSRequestInterface {
  protected $configuration;

  /**
   * UPSRequest constructor.
   *
   * @param $configuration
   */
  public function __construct($configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Returns authentication array for a request.
   *
   * @return array
   */
  protected function getAuth() {
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

  /**
   * Gets the rate setting: whether we will use negotiated rates or standard rates.
   *
   * @return mixed
   */
  public function getRateSetting() {
    return intval($this->configuration['api_information']['rate_setting']);
  }
}
