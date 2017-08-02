<?php
namespace Drupal\commerce_ups\Services;

use Ups\Entity\Package;
use Ups\Entity\Shipment;
use Ups\Entity\Shipper;
use Ups\Entity\ShipTo;

class UPSShipment {

  protected $shipment;
  protected $Shipper;
  protected $ShipToAddress;
  protected $package;

  /**
   * UPSPack constructor.
   *
   * @param \Ups\Entity\Shipper $Shipper
   * @param \Ups\Entity\ShipTo $ShipToAddress
   * @param \Ups\Entity\Package $package
   */
  public function __construct(Shipper $Shipper, ShipTo $ShipToAddress, Package $package) {
    //setup definitions
    $this->Shipper = $Shipper;
    $this->ShipToAddress = $ShipToAddress;
    $this->package = $package;
    $this->shipment = new Shipment();

    //setup shipment object with default values.
    $this->shipment->setShipper($this->Shipper);
    $this->shipment->setShipTo($this->ShipToAddress);
    $this->shipment->addPackage($this->package);

  }

  /**
   * @return \Ups\Entity\Shipment
   */
  public function getShipment() {
    return $this->shipment;
  }

  /**
   * @return \Ups\Entity\Shipper
   */
  public function getShipper() {
    return $this->Shipper;
  }

  /**
   * @return \Ups\Entity\ShipTo
   */
  public function getShipToAddress() {
    return $this->ShipToAddress;
  }

  /**
   * @return \Ups\Entity\Package
   */
  public function getPackage() {
    return $this->package;
  }

}