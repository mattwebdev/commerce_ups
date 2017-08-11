<?php

namespace Drupal\commerce_ups;

/**
 * UPS API Service.
 *
 * @package Drupal\commerce_ups
 */
class UPSRequest implements UPSRequestInterface {
  protected $configuration;

  public function __construct($configuration) {
    $this->configuration = $configuration;
  }

  public function __call($name, $arguments) {
    // TODO: Implement __call() method.
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
  public function getMode(array $configuration) {
    $mode = 'test';

    if (!empty($configuration['api_information']['mode'])) {
      $mode = $configuration['api_information']['mode'];
    }

    return $mode;
  }
}