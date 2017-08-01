<?php
/**
 * Created by PhpStorm.
 * User: mattheinke
 * Date: 8/1/17
 * Time: 12:28 PM
 */

namespace Drupal\commerce_ups\Controller;


use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_ups\Plugin\Commerce\ShippingMethod\CommerceUPS;
use Psy\Exception\Exception;

require(drupal_get_path('module', 'commerce_ups') . '/vendor/autoload.php');

class ups {

  public function GetUPSRate(ShipmentInterface $shipment, $configuration) {
    try {
      //UPS Access
      $accessKey = $configuration['access_key'];
      $userId = $configuration['user_id'];
      $password = $configuration['password'];
      //Commerce Data
      $store = $shipment->getOrder()->getStore();
      $ShippingProfile = $shipment->getShippingProfile();
      $ShippingProfileAddress = $shipment->getShippingProfile()->get('address');

      $rate = new \Ups\Rate($accessKey, $userId, $password);
      $rateRequest = new \Ups\Entity\RateRequest;
      //UPS Shippment object
      $shipmentObject = new \Ups\Entity\Shipment();

      //set Shipper address
      $shipperAddress = $shipmentObject->getShipper()->getAddress();
      $shipperAddress->setAddressLine1($store->getAddress()
        ->getAddressLine1());
      $shipperAddress->setAddressLine2($store->getAddress()
        ->getAddressLine2());
      $shipperAddress->setCity($store->getAddress()->getLocality());
      $shipperAddress->setStateProvinceCode($store->getAddress()
        ->get('administrative_area')
        ->getValue());
      $shipperAddress->setPostalCode($store->getAddress()->getPostalCode());
      $shipperAddress->setCountryCode($store->getAddress()->getCountryCode());

      //set ShipFrom
      $ShipFrom = new \Ups\Entity\ShipFrom();
      $ShipFrom->setAddress($shipperAddress);

      //set ShipTO
      $ShipTo = $shipmentObject->getShipTo();
      $ShipTo->setCompanyName($ShippingProfileAddress->first()
        ->getOrganization());
      $ShipTo->setAddress($this->BuildShipToAddress($shipment));

      $package = $this->BuildPackage($shipment);
      $shipmentObject->addPackage($package);

      $rateRequest = $rate->shopRates($shipmentObject);
    } catch (Exception $e) {
      $rateRequest = $e;
    }
    return $rateRequest;

  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   * @param \Ups\Entity\Shipment $shipmentObject
   *
   * @return \Ups\Entity\Address
   * @internal param \Ups\Entity\Shipment $ShipmentObject
   *
   */
  public function BuildShipToAddress(ShipmentInterface $shipment) {
    $ShippingProfileAddress = $shipment->getShippingProfile()->get('address');
    $ShipToAddress = new \Ups\Entity\Address();
    $ShipToAddress->setAddressLine1($ShippingProfileAddress->first()
      ->getAddressLine1());
    $ShipToAddress->setAddressLine2($ShippingProfileAddress->first()
      ->getAddressLine2());
    $ShipToAddress->setCity($ShippingProfileAddress->first()->getLocality());
    $ShipToAddress->setStateProvinceCode($ShippingProfileAddress->first()
      ->getAdministrativeArea());
    $ShipToAddress->setPostalCode($ShippingProfileAddress->first()
      ->getPostalCode());

    return $ShipToAddress;
  }

  public function BuildPackage(ShipmentInterface $shipment) {
    //Set Package
    $package = new \Ups\Entity\Package();

    $package->getPackagingType()
      ->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);

    $package->getPackageWeight()->setWeight($this->getPackageWeight($shipment)
      ->getWeight());
    $package->getPackageWeight()
      ->setUnitOfMeasurement($this->getPackageWeight($shipment)
        ->getUnitOfMeasurement());
    $package->setDimensions($this->setDimensions($shipment));
    return $package;
  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return \Ups\Entity\PackageWeight
   */
  public function getPackageWeight(ShipmentInterface $shipment) {
    $orderItems = $shipment->getOrder()->getItems();
    $itemWeight = [];
    foreach ($orderItems as $item) {
      $weight = $item->getPurchasedEntity()
        ->get('weight')
        ->getValue()[0]['number'];
      $quantity = $item->getQuantity();
      $orderItemWeight = floatval($weight) * intval($quantity);
      array_push($itemWeight, $orderItemWeight);
    }
    $upsWeight = new \Ups\Entity\PackageWeight();
    $upsWeight->setWeight(array_sum($itemWeight));
    $upsWeight->setUnitOfMeasurement($this->setWeightUnit($shipment));
    return $upsWeight;
  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return \Ups\Entity\UnitOfMeasurement
   */
  public function setWeightUnit(ShipmentInterface $shipment) {
    $unit = new \Ups\Entity\UnitOfMeasurement();
    $orderItems = $shipment->getOrder()->getItems();
    foreach ($orderItems as $item) {
      //we only need one unit because a package must have all the same weight unit so the last one is just as good as any.
      $ItemUnit = $item->getPurchasedEntity()
        ->get('weight')
        ->getValue()[0]['unit'];
    }
    //making sure that at least 1 item is in the order...if not, set to pounds.
    if (!isset($ItemUnit)) {
      $unit->setCode(\Ups\Entity\UnitOfMeasurement::PROD_POUNDS);

    }
    else {

      switch ($unit) {
        case 'lb':
          $unit->setCode(\Ups\Entity\UnitOfMeasurement::PROD_POUNDS);
      }

    }

    return $unit;
  }

  public function setDimensions(ShipmentInterface $shipment) {
    //Set Dims
    $dimensions = new \Ups\Entity\Dimensions();
    $dimensions->setHeight($this->getPackageHeight($shipment));
    $dimensions->setWidth($this->getPackageWidth($shipment));
    $dimensions->setLength($this->getPackageLength($shipment));
    $dimensions->setUnitOfMeasurement($this->setDimUnit());

    return $dimensions;
  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return int
   */
  public function getPackageHeight(ShipmentInterface $shipment) {
    //$items = $shipment->getOrder();

    return 10;
  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return int
   */
  public function getPackageWidth(ShipmentInterface $shipment) {
    //$items = $shipment->getOrder();

    return 10;

  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return int
   */
  public function getPackageLength(ShipmentInterface $shipment) {
    //$items = $shipment->getOrder();

    return 10;

  }

  public function setDimUnit() {
    //Set Unit
    $unit = new \Ups\Entity\UnitOfMeasurement;
    $unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);

    return $unit;
  }

  public function TranslateServiceCodeToString($serviceCode) {
    switch ($serviceCode) {
      //Domestic
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

}