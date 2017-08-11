<?php

namespace Drupal\commerce_ups;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Ups\Rate;

/**
 * Class UPSRateRequest
 * @package Drupal\commerce_ups
 */
class UPSRateRequest extends UPSRequest {
  /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface  */
  protected $commerce_shipment;

  /** @var array */
  protected $configuration;

  /** @var UPSShipment */
  protected $ups_shipment;

  /**
   * UPSRateRequest constructor.
   * @param array $configuration
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $commerce_shipment
   */
  public function __construct(array $configuration, ShipmentInterface $commerce_shipment) {
    parent::__construct($configuration);
    $this->commerce_shipment = $commerce_shipment;
  }

  /**
   * Fetch rates from the UPS API.
   */
  public function getRates() {
    $auth = $this->getAuth();

    $rate = new Rate(
      $auth['access_key'],
      $auth['user_id'],
      $auth['password']
    );
    try {
      $shipment = new UPSShipment($this->commerce_shipment);
      //$rate->getRate($shipment);
      $upsRate = $rate->shopRates($shipment);
    }
    catch (\Exception $ex) {
      $upsRate = $ex;
    }
    return $upsRate;
  }
}
