<?php

namespace Drupal\commerce_ups;
use Drupal\address\AddressInterface;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Ups\Entity\Package as UPSPackage;
use Ups\Entity\UnitOfMeasurement;
use Ups\Rate;
use UPS\Entity\Address;
use UPS\Entity\Shipment as UPSShipment;
use UPS\Entity\Dimensions;


class UPSRateRequest extends UPSRequest {
  protected $shipment;
  protected $configuration;
  protected $ups_shipment;

  public function __construct(array $configuration, ShipmentInterface $shipment) {
    parent::__construct($configuration);
    $this->shipment = $shipment;
  }

  public function getRates() {
    $auth = $this->getAuth();

    $rate = new Rate(
      $auth['access_key'],
      $auth['user_id'],
      $auth['password']
    );
    try {
      $rate->getRate($this->getShipment());
      // todo: pares object and return rate
    }
    catch (\Exception $ex) {
      // todo: handle exceptions.
    }
  }

  /**
   * @return \UPS\Entity\Shipment
   */
  public function getShipment() {
    $ups_shipment = new UPSShipment();
    $this->setShipTo($ups_shipment);
    $this->setShipFrom($ups_shipment);
    $this->setPackage($ups_shipment);
    return $ups_shipment;
  }

  /**
   * @param $ups_shipment UPSShipment.
   */
  public function setShipTo(UPSShipment $ups_shipment) {
    // todo: set all address fields

    /** @var $address AddressInterface */
    $address = $this->shipment->getShippingProfile()->get('address');
    $to_address = new Address();
    $to_address->setAddressLine1($address->getAddressLine1());
    $to_address->setAddressLine2($address->getAddressLine2());
    $to_address->setCity($address->getDependentLocality());
    $to_address->setStateProvinceCode($address->getAdministrativeArea());
    $to_address->setPostalCode($address->getPostalCode());
    $ups_shipment->getShipTo()->setAddress($to_address);
  }

  /**
   * @param \UPS\Entity\Shipment $ups_shipment
   */
  public function setShipFrom(UPSShipment $ups_shipment) {
    // todo: set all address fields.

    $address = $this->shipment->getOrder()->getStore()->getAddress();
    $from_address = new Address();
    $from_address->setAddressLine1($address->getAddressLine1());
    $from_address->setAddressLine2($address->getAddressLine2());
    $from_address->setCity($address->getDependentLocality());
    $from_address->setStateProvinceCode($address->getAdministrativeArea());
    $from_address->setPostalCode($address->getPostalCode());
    $ups_shipment->getShipFrom()->setAddress($from_address);
  }

  /**
   * @param \UPS\Entity\Shipment $ups_shipment
   */
  public function setPackage(UPSShipment $ups_shipment) {
    $package = new UPSPackage();
    $this->setDimensions($package);
    $this->setWeight($package);
    $ups_shipment->addPackage($package);
  }

  /**
   * @param \Ups\Entity\Package $ups_package
   */
  public function setDimensions(UPSPackage $ups_package) {
    $dimensions = new Dimensions();
    $dimensions->setHeight($this->shipment->getPackageType()->getHeight()->getNumber());
    $dimensions->setWidth($this->shipment->getPackageType()->getWidth()->getNumber());
    $dimensions->setLength($this->shipment->getPackageType()->getLength()->getNumber());
    $unit = $this->getUnitOfMeasure($this->shipment->getPackageType()->getLength()->getUnit());
    $dimensions->setUnitOfMeasurement($unit);
    $ups_package->setDimensions($dimensions);
  }

  /**
   * @param \Ups\Entity\Package $ups_package
   */
  public function setWeight(UPSPackage $ups_package) {
    $ups_package_weight = $ups_package->getPackageWeight();
    $ups_package_weight->setWeight($this->shipment->getPackageType()->getWeight()->getNumber());
    $unit = $this->getUnitOfMeasure($this->shipment->getPackageType()->getWeight()->getUnit());
    $ups_package_weight->setUnitOfMeasurement($unit);
  }

  /**
   * Convert commerce UOM to UPS API UOM.
   * @param $unit
   * @return string
   */
  public function getUnitOfMeasure($unit) {
    // todo: map all required units.
    switch ($unit) {
      case 'LBS':
        return UnitOfMeasurement::PROD_POUNDS;
      case 'KGS':
        return UnitOfMeasurement::PROD_KILOGRAMS;
      case 'IN':
        return UnitOfMeasurement::UOM_IN;
      case 'CM':
        return UnitOfMeasurement::UOM_CM;
    }
  }
}