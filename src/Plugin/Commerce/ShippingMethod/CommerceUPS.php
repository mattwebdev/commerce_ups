<?php

namespace Drupal\commerce_ups\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_ups\Controller\ups;

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
        'access_key' => '',
        'user_id' => '',
        'password' => '',
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
      $ups = new ups;
      $UpsRates = $ups->GetUPSRate($shipment,$this->configuration);
      foreach ($UpsRates as $upsRateObject) {
        foreach ($upsRateObject as $upsRate) {
          $cost = $upsRate->TotalCharges->MonetaryValue;
          $currency = $upsRate->TotalCharges->CurrencyCode;

          $price = new Price((string) $cost, $currency);
          $ServiceCode = $upsRate->Service->getCode();

          $shippingService = new ShippingService(
            $ServiceCode,
            $ups->TranslateServiceCodeToString($ServiceCode)
          );

          $rates[] = new ShippingRate(
            $ServiceCode,
            $shippingService,
            $price
          );

        }
      }
    }
    return $rates;
  }
}
