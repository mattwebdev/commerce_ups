<?php

namespace Drupal\commerce_ups\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Psy\Exception\Exception;

//drupal doesnt autoload from modules yet.
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/NodeInterface.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/RequestInterface.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Exception/RequestException.php');

require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Ups.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Rate.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Request.php');

require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Shipping.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/Address.php');

require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/Shipment.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/Shipper.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/ShipTo.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/ShipFrom.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/ReferenceNumber.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/Package.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/PackageWeight.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/PackageServiceOptions.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/Dimensions.php');

require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/PackagingType.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/UnitOfMeasurement.php');

require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/ShipmentServiceOptions.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/CallTagARS.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/Service.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/RateRequest.php');
require(drupal_get_path('module', 'commerce_ups') . '/vendor/gabrielbull/ups-api/src/Entity/PickupType.php');


/**
 * @CommerceShippingMethod(
 *  id = "ups",
 *  label = @Translation("UPS"),
 *  services = {
 *   "UPS Next Day Air",
 *   "UPS Second Day Air",
 *   "UPS Ground",
 *   "UPS Worldwide Express",
 *   "UPS Worldwide Expedited",
 *   "UPS Standard",
 *   "UPS Three-Day Select",
 *   "Next Day Air Saver",
 *   "UPS Next Day Air Early AM",
 *   "UPS Worldwide Express Plus",
 *   "UPS Second Day Air AM",
 *   "UPS Saver",
 *   "UPS Access Point Economy",
 *   "UPS Sure Post"
 *   },
 * )
 */
class CommerceUPS extends ShippingMethodBase {

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
   * Constructs a new ShippingMethodBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $packageTypeManager
   *
   * @param \Psr\Log\LoggerInterface $watchdog
   *
   * @internal param \Drupal\commerce_shipping\PackageTypeManagerInterface
   *   $package_type_manager The package type manager.*   The package type
   *   manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PackageTypeManagerInterface $packageTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $packageTypeManager);
    $this->packageTypeManager = $packageTypeManager;
    foreach ($this->pluginDefinition['services'] as $id => $label) {
      $this->services[$id] = new ShippingService($id, (string) $label);
    }
    $this->setConfiguration($configuration);
  }

  public function getServices() {

    // Filter out shipping services disabled by the merchant.
    return array_intersect_key($this->services, array_flip($this->configuration['services']));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'access_key' => '6D243D847D3796A8',
        'user_id' => 'fypweb',
        'password' => 'ytraPruoYroF%2017',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['access_key'] = [
      '#type' => 'textfield',
      '#title' => t('Access Key'),
      '#description' => t(''),
      '#default_value' => $this->configuration['access_key'],
      '#required' => TRUE,
    ];
    $form['user_id'] = [
      '#type' => 'textfield',
      '#title' => t('User ID'),
      '#description' => t(''),
      '#default_value' => $this->configuration['user_id'],
      '#required' => TRUE,
    ];
    $form['password'] = [
      '#type' => 'textfield',
      '#title' => t('Password'),
      '#description' => t(''),
      '#default_value' => $this->configuration['password'],
      '#required' => TRUE,
    ];

    return $form;
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
    //Rates Array
    $rates = [];

    if ($shipment->getShippingProfile()->address->isEmpty()) {
      $rates = [];
    }
    else {
        $UpsRate = $this->GetUPSRate($shipment);
        $cost = $UpsRate->RatedShipment[0]->TotalCharges->MonetaryValue;
        $currency = $UpsRate->RatedShipment[0]->TotalCharges->CurrencyCode;
        $price = new Price((string) $cost,$currency);
        $ServiceCode =$UpsRate->RatedShipment[0]->Service->getCode();
        $shippingService = new ShippingService(
          $ServiceCode,
          $this->TranslateServiceCodeToString($ServiceCode)
        );
        $rates[] = new ShippingRate(
          $ServiceCode,
          $shippingService,
          $price
        );
        return $rates;
    }
  }
  protected function TranslateServiceCodeToString($serviceCode) {
    switch($serviceCode) {
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

  protected function GetUPSRate(ShipmentInterface $shipment) {
  try {
    //UPS Access
    $accessKey = $this->configuration['access_key'];
    $userId = $this->configuration['user_id'];
    $password = $this->configuration['password'];
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

    $rateRequest = $rate->getRate($shipmentObject);
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
  protected function BuildShipToAddress(ShipmentInterface $shipment) {
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

  protected function BuildPackage(ShipmentInterface $shipment) {
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
  protected function getPackageWeight(ShipmentInterface $shipment) {
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
  protected function setWeightUnit(ShipmentInterface $shipment) {
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

  protected function setDimensions(ShipmentInterface $shipment) {
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
  protected function getPackageHeight(ShipmentInterface $shipment) {
    return 10;

  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return int
   */
  protected function getPackageWidth(ShipmentInterface $shipment) {
    return 10;

  }

  /**
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @return int
   */
  protected function getPackageLength(ShipmentInterface $shipment) {
    return 10;

  }

  protected function setDimUnit() {
    //Set Unit
    $unit = new \Ups\Entity\UnitOfMeasurement;
    $unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);

    return $unit;
  }

}