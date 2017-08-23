<?php

namespace Drupal\commerce_ups;

interface UPSRequestInterface {

  /**
   * Set the request configuration.
   *
   * @param array $configuration
   *   A configuration array from a CommerceShippingMethod.
   */
  public function setConfig(array $configuration);

}
