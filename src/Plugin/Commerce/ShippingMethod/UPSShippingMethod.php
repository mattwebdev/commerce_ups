<?php

namespace Drupal\commerce_ups\Plugin\CommerceShippingMethod;

use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Exception;
use Ups\Entity\Dimensions;
use Ups\Entity\Shipment;
use Ups\Entity\UnitOfMeasurement;
use Ups\Rate;
use Ups\Ups;

/**
 * @CommerceShippingMethod(
 *  id = "commerce_shipping_method",
 *  label = @Translation("UPS"),
 *  services = "array",
 * )
 */
class UPSShippingMethod extends ShippingMethodBase {

  /**
   * The package type manager.
   *
   * @var \Drupal\commerce_shipping\PackageTypeManagerInterface
   */
  protected $packageTypeManager;

  /**
   * The shipping services.
   *
   * @var \Drupal\commerce_shipping\ShippingService[]
   */
  protected $services = [];

  /**
   * {@inheritdoc}
   */
  public function getServices() {
    // Filter out shipping services disabled by the merchant.
    return array_intersect_key($this->services, array_flip($this->configuration['services']));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function selectRate(ShipmentInterface $shipment, ShippingRate $rate) {
    $shipment->setShippingService($rate->getService()->getId());
    $shipment->setAmount($rate->getAmount());
  }

  /**
   * {@inheritdoc}
   */
  public function calculateRates(ShipmentInterface $shipment) {
    $accessKey = '';
    $userId = '';
    $password = '';

    if ($shipment->getShippingProfile()->address->isEmpty()) {
      return [];
    } else {
      $rate = new Rate($accessKey, $userId, $password);

      try {
        $shipment = new Shipment();

        $shipperAddress = $shipment->getShipper()->getAddress();
        $shipperAddress->setPostalCode('99205');

        $address = new \Ups\Entity\Address();
        $address->setPostalCode('99205');
        $shipFrom = new \Ups\Entity\ShipFrom();
        $shipFrom->setAddress($address);

        $shipment->setShipFrom($shipFrom);

        $shipTo = $shipment->getShipTo();
        $shipTo->setCompanyName('Test Ship To');
        $shipToAddress = $shipTo->getAddress();
        $shipToAddress->setPostalCode('99205');

        $package = new \Ups\Entity\Package();
        $package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);
        $package->getPackageWeight()->setWeight(10);

        $weightUnit = new UnitOfMeasurement;
        $weightUnit->setCode(UnitOfMeasurement::UOM_KGS);
        $package->getPackageWeight()->setUnitOfMeasurement($weightUnit);

        $dimensions = new Dimensions();
        $dimensions->setHeight(10);
        $dimensions->setWidth(10);
        $dimensions->setLength(10);

        $unit = new UnitOfMeasurement;
        $unit->setCode(UnitOfMeasurement::UOM_IN);

        $dimensions->setUnitOfMeasurement($unit);
        $package->setDimensions($dimensions);

        $shipment->addPackage($package);

        return $rate->getRate($shipment);

      } catch (Exception $e) {
        var_dump($e);
      }
    }
  }

}
