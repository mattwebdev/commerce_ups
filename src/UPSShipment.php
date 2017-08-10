<?php

namespace Drupal\commerce_ups;
use Drupal\address\AddressInterface;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Ups\Entity\Package as UPSPackage;
use UPS\Entity\Address;
use UPS\Entity\Shipment as APIShipment;
use UPS\Entity\Dimensions;
use Ups\Entity\UnitOfMeasurement;

class UPSShipment extends UPSEntity {
  protected $shipment;
  protected $api_shipment;

  public function __construct(ShipmentInterface $shipment) {
    parent::__construct();
    $this->shipment = $shipment;
  }

  /**
   * @return \UPS\Entity\Shipment
   */
  public function getShipment() {
    $api_shipment = new APIShipment();
    $this->setShipTo($api_shipment);
    $this->setShipFrom($api_shipment);
    $this->setPackage($api_shipment);
    return $api_shipment;
  }

  /**
   * @param $api_shipment APIShipment.
   */
  public function setShipTo(APIShipment $api_shipment) {
    // todo: set all address fields

    /** @var $address AddressInterface */
    $address = $this->shipment->getShippingProfile()->get('address');
    $to_address = new Address();
    $to_address->setAddressLine1($address->getAddressLine1());
    $to_address->setAddressLine2($address->getAddressLine2());
    $to_address->setCity($address->getDependentLocality());
    $to_address->setStateProvinceCode($address->getAdministrativeArea());
    $to_address->setPostalCode($address->getPostalCode());
    $api_shipment->getShipTo()->setAddress($to_address);
  }

  /**
   * @param \UPS\Entity\Shipment $api_shipment
   */
  public function setShipFrom(APIShipment $api_shipment) {
    // todo: set all address fields.

    $address = $this->shipment->getOrder()->getStore()->getAddress();
    $from_address = new Address();
    $from_address->setAddressLine1($address->getAddressLine1());
    $from_address->setAddressLine2($address->getAddressLine2());
    $from_address->setCity($address->getDependentLocality());
    $from_address->setStateProvinceCode($address->getAdministrativeArea());
    $from_address->setPostalCode($address->getPostalCode());
    $api_shipment->getShipFrom()->setAddress($from_address);
  }

  /**
   * @param \UPS\Entity\Shipment $api_shipment
   */
  public function setPackage(APIShipment $api_shipment) {
    $package = new UPSPackage();
    $this->setDimensions($package);
    $this->setWeight($package);
    $api_shipment->addPackage($package);
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
    $dimensions->setUnitOfMeasurement($this->setUnitOfMeasurement($unit));
    $ups_package->setDimensions($dimensions);
  }

  /**
   * @param \Ups\Entity\Package $ups_package
   */
  public function setWeight(UPSPackage $ups_package) {
    $ups_package_weight = $ups_package->getPackageWeight();
    $ups_package_weight->setWeight($this->shipment->getPackageType()->getWeight()->getNumber());
    $unit = $this->getUnitOfMeasure($this->shipment->getPackageType()->getWeight()->getUnit());
    $ups_package_weight->setUnitOfMeasurement($this->setUnitOfMeasurement($unit));
  }

  public function setUnitOfMeasurement($code) {
    $upsUnit = new UnitOfMeasurement();
    $upsUnit->setCode($code);
    return $upsUnit;
  }
}