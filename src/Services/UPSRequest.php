<?php

namespace Drupal\commerce_ups\Services;

use Psr\Log\LoggerInterface;
use Ups\Entity\Shipment;
use Ups\Rate;

/**
 * Class UPSRequest.
 */
class UPSRequest {

  /**
   * Commerce UPS Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  protected $configuration;

  protected $shipment;

  protected $rate;

  /**
   * Constructs a new UPSRequest object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Ups\Entity\Shipment $shipment
   * @param array $configuration
   */
  public function __construct(LoggerInterface $logger, Shipment $shipment, array $configuration) {
    $this->logger = $logger;
    $this->configuration = $configuration;
    $this->shipment = $shipment;
    $this->rate = new Rate();
  }

  /**
   * @return \Ups\Entity\RateResponse
   */
  public function getSingleRate() {
    return $this->rate->getRate($this->shipment);
  }

  /**
   * @return \Ups\Entity\RateResponse
   */
  public function getAllRates() {
    return $this->rate->shopRates($this->shipment);
  }

}
