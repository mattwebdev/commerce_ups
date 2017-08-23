<?php

namespace Drupal\commerce_ups;

use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Ups\Entity\Package as UPSPackage;
use Ups\Entity\Address;
use Ups\Entity\ShipFrom;
use Ups\Entity\Shipment as APIShipment;
use Ups\Entity\Dimensions;

class UPSShipment extends UPSEntity {
  protected $shipment;
  protected $api_shipment;

  /**
   * UPSShipment constructor.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   A commerce shipping shipment object.
   */
  public function __construct(ShipmentInterface $shipment) {
    parent::__construct();
    $this->shipment = $shipment;
  }

  /**
   * Creates and returns a Ups API shipment object.
   *
   * @return \Ups\Entity\Shipment
   *   A Ups API shipment object.
   */
  public function getShipment() {
    $api_shipment = new APIShipment();
    $this->setShipTo($api_shipment);
    $this->setShipFrom($api_shipment);
    $this->setPackage($api_shipment);
    return $api_shipment;
  }

  /**
   * Sets the ship to for a given shipment.
   *
   * @param \Ups\Entity\Shipment $api_shipment
   *   A Ups API shipment object.
   */
  public function setShipTo(APIShipment $api_shipment) {
    // todo: set all address fields.
    $address = $this->shipment->getShippingProfile()->address;
    $to_address = new Address();
    $to_address->setAddressLine1($address->address_line1);
    $to_address->setAddressLine2($address->address_line2);
    $to_address->setCity($address->locality);
    $to_address->setStateProvinceCode($address->administrative_area);
    $to_address->setPostalCode($address->postal_code);
    $api_shipment->getShipTo()->setAddress($to_address);
  }

  /**
   * Sets the ship from for a given shipment.
   *
   * @param \Ups\Entity\Shipment $api_shipment
   *   A Ups API shipment object.
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
    $ship_from = new ShipFrom();
    $ship_from->setAddress($from_address);
    $api_shipment->setShipFrom($ship_from);
  }

  /**
   * Sets the package for a given shipment.
   *
   * @param \Ups\Entity\Shipment $api_shipment
   *   A Ups API shipment object.
   */
  public function setPackage(APIShipment $api_shipment) {
    $package = new UPSPackage();
    $this->setDimensions($package);
    $this->setWeight($package);
    $api_shipment->addPackage($package);
  }

  /**
   * Package dimension setter.
   *
   * @param \Ups\Entity\Package $ups_package
   *   A Ups API package object.
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
   * Define the package weight.
   *
   * @param \Ups\Entity\Package $ups_package
   *   A package object from the Ups API.
   */
  public function setWeight(UPSPackage $ups_package) {
    $ups_package_weight = $ups_package->getPackageWeight();
    $ups_package_weight->setWeight($this->shipment->getPackageType()->getWeight()->getNumber());
    $unit = $this->getUnitOfMeasure($this->shipment->getPackageType()->getWeight()->getUnit());
    $ups_package_weight->setUnitOfMeasurement($this->setUnitOfMeasurement($unit));
  }

}
