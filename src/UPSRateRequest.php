<?php

namespace Drupal\commerce_ups;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Ups\Rate;


class UPSRateRequest extends UPSRequest {
  protected $commerce_shipment;
  protected $configuration;
  protected $ups_shipment;

  public function __construct(array $configuration, ShipmentInterface $commerce_shipment) {
    parent::__construct($configuration);
    $this->commerce_shipment = $commerce_shipment;
  }

  public function getRates() {
    $auth = $this->getAuth();

    $rate = new Rate(
      $auth['access_key'],
      $auth['user_id'],
      $auth['password']
    );
    try {
      $shipment = new UPSShipment($this->commerce_shipment);
      $rate->getRate($shipment);
      // todo: pares object and return rate
    }
    catch (\Exception $ex) {
      // todo: handle exceptions.
    }
  }
}