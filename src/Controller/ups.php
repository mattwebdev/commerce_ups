<?php
namespace Drupal\commerce_ups\Controller;


use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Psy\Exception\Exception;

// @todo Move this into root /src directory, define as a service.
class ups {

  // @todo getRate, camel case.
  public function GetUPSRate(ShipmentInterface $shipment, $configuration) {
    try {
      //UPS Access
      $accessKey = $configuration['access_key'];
      $userId = $configuration['user_id'];
      $password = $configuration['password'];
      //Commerce Data
      $store = $shipment->getOrder()->getStore();
      /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $ShippingProfileAddress */
      $ShippingProfileAddress = $shipment->getShippingProfile()->get('address')->first();

      // @todo import, no need for full class name.
      $rate = new \Ups\Rate($accessKey, $userId, $password);
      //UPS Shippment object
      // @todo import, no need for full class name.
      $shipmentObject = new \Ups\Entity\Shipment();

      //set Shipper address
      $shipperAddress = $shipmentObject->getShipper()->getAddress();
      $shipperAddress->setAddressLine1($store->getAddress()->getAddressLine1());
      $shipperAddress->setAddressLine2($store->getAddress()->getAddressLine2());
      $shipperAddress->setCity($store->getAddress()->getLocality());
      $shipperAddress->setStateProvinceCode($store->getAddress()->getAdministrativeArea());
      $shipperAddress->setPostalCode($store->getAddress()->getPostalCode());
      $shipperAddress->setCountryCode($store->getAddress()->getCountryCode());

      //set ShipFrom
      // @todo import, no need for full class name.
      $ShipFrom = new \Ups\Entity\ShipFrom();
      $ShipFrom->setAddress($shipperAddress);

      //set ShipTO
      $ShipTo = $shipmentObject->getShipTo();
      $ShipTo->setCompanyName($ShippingProfileAddress->getOrganization());
      $ShipTo->setAddress($this->BuildShipToAddress($shipment));

      $package = $this->BuildPackage($shipment);
      $shipmentObject->addPackage($package);

      $rateRequest = $rate->shopRates($shipmentObject);
    }
    catch (\Exception $e) {
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
   * @todo camel case, buildShipToAddress
   *
   */
  public function BuildShipToAddress(ShipmentInterface $shipment) {
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $ShippingProfileAddress */
    $ShippingProfileAddress = $shipment->getShippingProfile()->get('address')->first();
    // @todo import, no need for full class name.
    $ShipToAddress = new \Ups\Entity\Address();
    $ShipToAddress->setAddressLine1($ShippingProfileAddress->getAddressLine1());
    $ShipToAddress->setAddressLine2($ShippingProfileAddress->getAddressLine2());
    $ShipToAddress->setCity($ShippingProfileAddress->getLocality());
    $ShipToAddress->setStateProvinceCode($ShippingProfileAddress->getAdministrativeArea());
    $ShipToAddress->setPostalCode($ShippingProfileAddress->getPostalCode());
    return $ShipToAddress;
  }

  // @todo camel case, buildPackage
  public function BuildPackage(ShipmentInterface $shipment) {
    //Set Package
    // @todo import, no need for full class name.
    $package = new \Ups\Entity\Package();
    // @todo import, no need for full class name.
    $package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);

    $package->getPackageWeight()->setWeight($this->getPackageWeight($shipment)->getWeight());
    $package->getPackageWeight()->setUnitOfMeasurement($this->getPackageWeight($shipment)->getUnitOfMeasurement());
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
      // @todo check fedex, seems like this should be easier.
      $weight = $item->getPurchasedEntity()->get('weight')->value;
      $quantity = $item->getQuantity();
      $orderItemWeight = floatval($weight) * intval($quantity);
      array_push($itemWeight, $orderItemWeight);
    }
    // @todo import, no need for full class name.
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
    // @todo import, no need for full class name.
    $unit = new \Ups\Entity\UnitOfMeasurement();
    $orderItems = $shipment->getOrder()->getItems();
    foreach ($orderItems as $item) {
      //we only need one unit because a package must have all the same weight unit so the last one is just as good as any.
      $ItemUnit = $item->getPurchasedEntity()
        ->get('weight')->unit;
    }
    //making sure that at least 1 item is in the order...if not, set to pounds.
    if (!isset($ItemUnit)) {
      // @todo import, no need for full class name.
      $unit->setCode(\Ups\Entity\UnitOfMeasurement::PROD_POUNDS);

    }
    else {

      switch ($unit) {
        case 'lb':
          // @todo import, no need for full class name.
          $unit->setCode(\Ups\Entity\UnitOfMeasurement::PROD_POUNDS);
      }

    }

    return $unit;
  }

  public function setDimensions(ShipmentInterface $shipment) {
    //Set Dims
    // @todo import, no need for full class name.
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
    $items = $shipment->getOrder()->getItems();
    $heights = [];
    foreach($items as $item) {
      $heights[] = floatval($item->getPurchasedEntity()->get('dimensions')->first()->getHeight()->getNumber());
    }
    return max($heights);
  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return int
   */
  public function getPackageWidth(ShipmentInterface $shipment) {
    $items = $shipment->getOrder()->getItems();
    $widths = [];
    foreach($items as $item) {
      $widths[] = floatval($item->getPurchasedEntity()->get('dimensions')->first()->getWidth()->getNumber());
    }

    return max($widths);

  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return int
   */
  public function getPackageLength(ShipmentInterface $shipment) {
    $items = $shipment->getOrder()->getItems();
    $lengths = [];
    foreach($items as $item) {
      $lengths[] = floatval($item->getPurchasedEntity()->get('dimensions')->first()->getLength()->getNumber());
    }

    return max($lengths);

  }

  public function setDimUnit() {
    //Set Unit
    // @todo import, no need for full class name.
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
