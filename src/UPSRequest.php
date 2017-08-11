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
   * Gets the mode to use for API calls.
   *
   * @param array $configuration
   *   The shipping method configuration array.
   *
   * @return string
   *   The mode (test or live).
   */
  public function getMode() {
    $mode = 'test';

    if (!empty($this->configuration['api_information']['mode'])) {
      $mode = $this->configuration['api_information']['mode'];
    }

    return $mode;
  }

  /**
   * Gets the rate setting: whether we will use negotiated rates or standard rates.
   *
   * @return int
   */
  public function getRateSetting() {
    return intval($this->configuration['api_information']['rate_setting']);
  }
}
