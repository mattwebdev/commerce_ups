<?php

namespace Drupal\commerce_ups\Controller;

use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Exception;
use Ups\Entity\Address;
use Ups\Entity\Dimensions;
use Ups\Entity\Package;
use Ups\Entity\PackageWeight;
use Ups\Entity\PackagingType;
use Ups\Entity\RateInformation;
use Ups\Entity\ShipFrom;
use Ups\Entity\Shipment;
use Ups\Entity\ShipTo;
use Ups\Entity\UnitOfMeasurement;
use Ups\Rate;
use Ups\SimpleAddressValidation;

/**
 * @todo Move this into root /src directory, define as a service.
 */
class Ups {

  private $configuration;

  private $shipment;

  public function __construct($configuration, ShipmentInterface $shipment) {
    $this->configuration = $configuration;
    $this->shipment = $shipment;
  }

  /**
   * @param \Ups\Entity\Shipment $shipment
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *
   * @return \Ups\Entity\Address
   */
  public function setShiperAddress(Shipment $shipment, StoreInterface $store) {

    // Set Shipper address.
    $shipperAddress = $shipment->getShipper()->getAddress();
    $shipperAddress->setAddressLine1($store->getAddress()->getAddressLine1());
    $shipperAddress->setAddressLine2($store->getAddress()->getAddressLine2());
    $shipperAddress->setCity($store->getAddress()->getLocality());
    $shipperAddress->setStateProvinceCode($store->getAddress()
      ->getAdministrativeArea());
    $shipperAddress->setPostalCode($store->getAddress()->getPostalCode());
    $shipperAddress->setCountryCode($store->getAddress()->getCountryCode());

    return $shipperAddress;
  }

  /**
   * @param \Ups\Entity\Address $address
   *
   * @return \Ups\Entity\ShipFrom
   */
  public function setShipFromAddress(Address $address) {
    // Set ShipFrom.
    $ShipFrom = new ShipFrom();
    $ShipFrom->setAddress($address);

    return $ShipFrom;
  }

  /**
   * @param \Ups\Entity\Shipment $shipment
   * @param $ShippingProfileAddress
   *
   * @return \Ups\Entity\ShipTo
   */
  public function setShipToAddress(Shipment $shipment,$ShippingProfileAddress) {
    // Set ShipTO.
    $ShipTo = new ShipTo();
    $ShipTo = $shipment->getShipTo();
    $ShipTo->setCompanyName($ShippingProfileAddress->getOrganization());
    $ShipTo->setAddress($this->buildShipToAddress());

    return $ShipTo;
  }

  /**
   * @return \Exception|\Ups\Entity\RateResponse
   */
  public function getUpsRate() {
    try {
      // UPS Access.
      $accessKey = $this->configuration['access_key'];
      $userId = $this->configuration['user_id'];
      $password = $this->configuration['password'];

      //sets setNegotiatedRatesIndicator to 1 or 0 based on configuration
      $this->checkForNegotiatedRates();

      if ($this->configuration['testMode'] == 1) {
        $useIntegration = TRUE;
      }
      else {
        $useIntegration = FALSE;
      }

      // Commerce Data.
      $store = $this->shipment->getOrder()->getStore();
      /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $ShippingProfileAddress */
      $ShippingProfileAddress = $this->shipment->getShippingProfile()
        ->get('address')
        ->first();

      $rate = new Rate($accessKey, $userId, $password, $useIntegration);
      $package = $this->buildPackage();

      // UPS Shipment object.
      $shipmentObject = new Shipment();
      $shipperAddress = $this->setShiperAddress($shipmentObject, $store);

      $shipFrom = $this->setShipFromAddress($shipperAddress);
      $shipTo = $this->setShipToAddress($shipmentObject,$ShippingProfileAddress);

      $shipmentObject->setShipTo($shipTo);
      $shipmentObject->setShipFrom($shipFrom);

      $shipmentObject->addPackage($package);

      $rateRequest = $rate->shopRates($shipmentObject);
    } catch (\Exception $e) {
      $rateRequest = $e;
    }
    return $rateRequest;
  }

  public function checkForNegotiatedRates() {
    $rateInformation = new RateInformation;

    if ($this->configuration['nRate'] == 1) {
      $rateInformation->setNegotiatedRatesIndicator(1);
    }
    else {
      $rateInformation->setNegotiatedRatesIndicator(0);
    }

    return $rateInformation;
  }

  /**
   *
   * @return \Ups\Entity\Address
   */
  public function buildShipToAddress() {
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $ShippingProfileAddress */
    $ShippingProfileAddress = $this->shipment->getShippingProfile()
      ->get('address')
      ->first();
    $ShipToAddress = new Address();
    $ShipToAddress->setAddressLine1($ShippingProfileAddress->getAddressLine1());
    $ShipToAddress->setAddressLine2($ShippingProfileAddress->getAddressLine2());
    $ShipToAddress->setCity($ShippingProfileAddress->getLocality());
    $ShipToAddress->setStateProvinceCode($ShippingProfileAddress->getAdministrativeArea());
    $ShipToAddress->setPostalCode($ShippingProfileAddress->getPostalCode());
    // Verify the ship To address that was just created
    $verify = $this->verifySimpleAddress($ShipToAddress);
    // @todo - we should probably present the user with options, but for now, just take the most likely option.
    $ShipToAddress->setCity($verify[0]->Address->City);
    $ShipToAddress->setStateProvinceCode(($verify[0]->Address->StateProvinceCode));

    // Return ShipToAddress with the modifications made by the verification process
    return $ShipToAddress;
  }

  /**
   * @param \Ups\Entity\Address $address
   *
   * @return array
   */
  public function verifySimpleAddress(Address $address) {
    $validation = [];
    $av = new SimpleAddressValidation($this->configuration['access_key'], $this->configuration['user_id'], $this->configuration['password']);
    try {
      $validation = $av->validate($address);
    } catch (Exception $e) {
      var_dump($e);
    }
    return $validation;
  }

  /**
   *
   * @return \Ups\Entity\Package
   */
  public function buildPackage() {
    // Set Package.
    $package = new Package();
    $package->getPackagingType()->setCode(PackagingType::PT_PACKAGE);

    $package->getPackageWeight()->setWeight($this->getPackageWeight()
      ->getWeight());
    $package->getPackageWeight()->setUnitOfMeasurement($this->getPackageWeight()
      ->getUnitOfMeasurement());
    $package->setDimensions($this->setDimensions());
    return $package;
  }

  /**
   *
   * @return \Ups\Entity\PackageWeight
   */
  public function getPackageWeight() {
    $orderItems = $this->shipment->getOrder()->getItems();
    $itemWeight = [];
    foreach ($orderItems as $item) {
      // @todo check fedex, seems like this should be easier.
      $weight = $item->getPurchasedEntity()->get('weight')->first();
      $quantity = $item->getQuantity();
      $orderItemWeight = floatval($weight) * intval($quantity);
      array_push($itemWeight, $orderItemWeight);
    }
    $upsWeight = new PackageWeight();
    $upsWeight->setWeight(array_sum($itemWeight));
    $upsWeight->setUnitOfMeasurement($this->setWeightUnit());
    return $upsWeight;
  }

  /**
   *
   * @return \Ups\Entity\UnitOfMeasurement
   */
  public function setWeightUnit() {
    $unit = new UnitOfMeasurement();
    $orderItems = $this->shipment->getOrder()->getItems();
    foreach ($orderItems as $item) {
      // We only need one unit because a package must have all the same weight unit so the last one is just as good as any.
      $ItemUnit = $item->getPurchasedEntity()
        ->get('weight')->unit;
    }
    // Making sure that at least 1 item is in the order...if not, set to pounds.
    if (!isset($ItemUnit)) {
      $unit->setCode(UnitOfMeasurement::PROD_POUNDS);

    }
    else {

      switch ($unit) {
        case 'lb':
          $unit->setCode(UnitOfMeasurement::PROD_POUNDS);
      }

    }

    return $unit;
  }

  /**
   *
   * @return \Ups\Entity\Dimensions
   */
  public function setDimensions() {
    // Set Dims.
    $dimensions = new Dimensions();
    $dimensions->setHeight($this->getPackageHeight()->getNumber());
    $dimensions->setWidth($this->getPackageWidth()->getNumber());
    $dimensions->setLength($this->getPackageLength()->getNumber());
    $dimensions->setUnitOfMeasurement($this->setDimUnit());

    return $dimensions;
  }

  /**
   *
   * @return \Drupal\physical\Length
   */
  public function getPackageHeight() {
    $height = $this->shipment->getPackageType()->getHeight();
    return $height;
  }

  /**
   *
   * @return \Drupal\physical\Length
   */
  public function getPackageWidth() {
    $width = $this->shipment->getPackageType()->getWidth();
    return $width;

  }

  /**
   *
   * @return \Drupal\physical\Length
   */
  public function getPackageLength() {
    $length = $this->shipment->getPackageType()->getLength();
    return $length;
  }

  /**
   *
   */
  public function setDimUnit() {
    // Set Unit.
    $unit = new UnitOfMeasurement();
    $unit->setCode(UnitOfMeasurement::UOM_IN);

    return $unit;
  }

  /**
   * stub function for sending shippment to UPS.
   */
  public function sendShippment() {

  }

  public function translateServiceCodeToString($serviceCode) {
    switch ($serviceCode) {
      // Domestic.
      case 14:
        $service = "UPS Next Day Air Early";
        break;

      case 01:
        $service = "UPS Next Day Air";
        break;

      case 13:
        $service = "UPS Next Day Air Saver";
        break;

      case 59:
        $service = "UPS 2nd Day Air A.M.";
        break;

      case 02:
        $service = "UPS 2nd Day Air";
        break;

      case 12:
        $service = "UPS 3 Day Select";
        break;

      case 03:
        $service = "UPS Ground";
        break;

      default:
        $service = "UPS Ground";
        break;
    }
    return $service;
  }

  /**
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  public function getShipment() {
    return $this->shipment;
  }
}
