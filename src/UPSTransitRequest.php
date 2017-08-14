<?php
/**
 * Created by PhpStorm.
 * User: mattheinke
 * Date: 8/14/17
 * Time: 9:52 AM
 */

namespace Drupal\commerce_ups;

use DateInterval;
use DateTime;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\Core\Logger\LoggerChannel;
use Psr\Log\LoggerInterface;
use Ups\Entity\AddressArtifactFormat;
use Ups\Entity\InvoiceLineTotal;
use Ups\Entity\Shipment;
use Ups\Entity\ShipmentWeight;
use Ups\Entity\TimeInTransitRequest;
use Ups\Entity\UnitOfMeasurement;
use Ups\Request;
use Ups\TimeInTransit;

/**
 * Class UPSTransitRequest
 *
 * Extends UPSRateRequest so that we can access the rate information
 * without doing the calculations ourselves.
 *
 * @package Drupal\commerce_ups
 */
class UPSTransitRequest extends UPSRateRequest {

  protected $request;
  protected $ups_shipment;
  protected $commerce_shipment;

  public function __construct(array $configuration, ShipmentInterface $commerce_shipment, Shipment $api_shipment) {
    parent::__construct($configuration,$commerce_shipment);
    $this->commerce_shipment = $commerce_shipment;
    $this->request = new TimeInTransitRequest;
    $this->ups_shipment = $api_shipment;
  }

  public function getTransitTime() {
    $auth = $this->getAuth();

    $time_in_transit = new TimeInTransit(
      $auth['access_key'],
      $auth['user_id'],
      $auth['password']
    );

    $this->setAddressArtifacts();
    $this->setInvoiceLines();
    $this->setWeight();
    $this->setPickup();
    $this->setPackageCount();


    die(kint($time_in_transit->getTimeInTransit($this->request)));

  }


  public function setAddressArtifacts() {
    $ship_from_artifact = new AddressArtifactFormat;
    $ship_to_artifact = new AddressArtifactFormat;


    $ship_from_artifact->setPoliticalDivision3($this->commerce_shipment->getOrder()->getStore()->getAddress()->getLocality());
    $ship_from_artifact->setPostcodePrimaryLow($this->commerce_shipment->getOrder()->getStore()->getAddress()->getPostalCode());
    $ship_from_artifact->setCountryCode($this->commerce_shipment->getOrder()->getStore()->getAddress()->getCountryCode());

    $ship_to_artifact->setPoliticalDivision3($this->ups_shipment->getShipTo()->getAddress()->getCity());
    $ship_to_artifact->setPostcodePrimaryLow($this->ups_shipment->getShipTo()->getAddress()->getPostalCode());
    $ship_to_artifact->setCountryCode($this->ups_shipment->getShipTo()->getAddress()->getCountryCode());

    $artifacts = [
      'ship_from' => $ship_from_artifact,
      'ship_to' => $ship_to_artifact
    ];

    $this->request->setTransitFrom($artifacts['ship_from']);
    $this->request->setTransitTo($artifacts['ship_to']);
  }

  public function setInvoiceLines() {
    $invoiceLineTotal = new InvoiceLineTotal;
    $invoiceLineTotal->setMonetaryValue($this->commerce_shipment->getOrder()->getSubtotalPrice()->getNumber());
    $invoiceLineTotal->setCurrencyCode($this->commerce_shipment->getOrder()->getSubtotalPrice()->getNumber());

    $this->request->setInvoiceLineTotal($invoiceLineTotal);

  }

  public function setPickup() {
    $date = new DateTime();
    //set statically for now, there should be a "production days" value somewhere...
    $date->add(new DateInterval('P8D'));

    $this->request->setPickupDate($date);

  }

  public function setWeight() {
    $shipWeight = new ShipmentWeight();
    $commerce_weight = $this->commerce_shipment->getWeight()->getNumber();
    $shipWeight->setWeight($commerce_weight);
    $this->request->setShipmentWeight($shipWeight);
  }

  public function setPackageCount() {
    $packages = $this->ups_shipment->getPackages();
    $package_count = 0;
    foreach($packages as $package) {
      $package_count++;
    }

    $this->request->setTotalPackagesInShipment($package_count);

  }

}