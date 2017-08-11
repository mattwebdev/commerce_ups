<?php

namespace Drupal\commerce_ups;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Ups\Rate;
use Ups\Entity\RateInformation;


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
    $rates = [];
    $auth = $this->getAuth();

    $request = new Rate(
      $auth['access_key'],
      $auth['user_id'],
      $auth['password'],
      $this->useIntegrationMode()
    );

    try {
      $ups_shipment = new UPSShipment($this->commerce_shipment);
      $shipment = $ups_shipment->getShipment();
      // Set rate information.
      $rateInformation = new RateInformation;
      $rateInformation->setNegotiatedRatesIndicator($this->getRateSetting());
      // Shop Rates
      $ups_rates = $request->shopRates($shipment);
    }
    catch (\Exception $ex) {
      // todo: handle exceptions by logging.
      $ups_rates = [];
    }

    if (!empty($ups_rates->RatedShipment)) {
      foreach ($ups_rates->RatedShipment as $ups_rate) {
        $cost = $ups_rate->TotalCharges->MonetaryValue;
        $currency = $ups_rate->TotalCharges->CurrencyCode;
        $price = new Price((string) $cost, $currency);
        $service_code = $ups_rate->Service->getCode();
        $service_name = $ups_rate->Service->getName();
        $shipping_service = new ShippingService(
          $service_name,
          $service_name
        );
        $rates[] = new ShippingRate(
          $service_code,
          $shipping_service,
          $price
        );
      }
    }
    return $rates;
  }
}
