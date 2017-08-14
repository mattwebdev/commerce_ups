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
      $rate_information = new RateInformation;
      $rate_information->setNegotiatedRatesIndicator($this->getRateSetting());
      $shipment->setRateInformation($rate_information);

      // Shop Rates
      $ups_rates = $request->shopRates($shipment);
    }
    catch (\Exception $ex) {
      // todo: handle exceptions by logging.
    }

    if (isset($ups_rates) && !empty($ups_rates->RatedShipment)) {
      foreach ($ups_rates->RatedShipment as $ups_rate) {
        $service_code = $ups_rate->Service->getCode();

        // Only add the rate if this service is enabled.
        if (!in_array($service_code, $this->configuration['services'])) {
          continue;
        }
        // Check whether we are using negotiated rates or standard rates.
        if($this->getRateSetting() == 1 && !empty($ups_rate->NegotiatedRates)) {
          // Use negotiated rates.
          $cost = $ups_rate->NegotiatedRates->GrandTotal->MonetaryValue;
          $currency = $ups_rate->NegotiatedRates->GrandTotal->CurrencyCode;
        } else {
          // Use standard rates.
          $cost = $ups_rate->TotalCharges->MonetaryValue;
          $currency = $ups_rate->TotalCharges->CurrencyCode;
        }

        $price = new Price((string) $cost, $currency);
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

  /**
   * Gets the rate setting: whether we will use negotiated rates or standard rates.
   *
   * @return boolean
   */
  public function getRateSetting() {
    return boolval($this->configuration['api_information']['rate_setting']);
  }

}
