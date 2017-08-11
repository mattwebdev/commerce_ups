<?php

namespace Drupal\commerce_ups;

interface UPSRequestInterface {
  /**
   * Set the request configuration.
   * @param $configuration
   */
  public function setConfig($configuration);
}
