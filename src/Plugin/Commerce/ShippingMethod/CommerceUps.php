<?php

namespace Drupal\commerce_ups\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_ups\Controller\Ups;
use Exception;

/**
 * @CommerceShippingMethod(
 *  id = "ups",
 *  label = @Translation("UPS"),
 *  services = {
 *   "UPS_NEXT_DAY_AIR" = @translation("UPS Next Day Air"),
 *   "UPS_SECOND_DAY_AIR" = @translation("UPS Second Day Air"),
 *   "UPS_GROUND" = @translation("UPS Ground"),
 *   "UPS_WORLDWIDE_EXPRESS" = @translation("UPS Worldwide Express"),
 *   "UPS_WORLDWIDE_EXPEDITED" = @translation("UPS Worldwide Expedited"),
 *   "UPS_STANDARD" = @translation("UPS Standard"),
 *   "UPS_THREE-DAY_SELECT" = @translation("UPS Three-Day Select"),
 *   "UPS_NEXT_DAY_AIR_SAVER" = @translation("Next Day Air Saver"),
 *   "UPS_NEXT_DAY_AIR_EARLY_AM" = @translation("UPS Next Day Air Early AM"),
 *   "UPS_WORLDWIDE_EXPRESS_PLUS" = @translation("UPS Worldwide Express Plus"),
 *   "UPS_SECOND_DAY_AIR_AM" = @translation("UPS Second Day Air AM"),
 *   "UPS_SAVER" = @translation("UPS Saver"),
 *   "UPS_ACCESS_POINT_ECONOMY" = @translation("UPS Access Point Economy"),
 *   "UPS_SURE_POST" = @translation("UPS Sure Post"),
 *   },
 * )
 */
class CommerceUps extends ShippingMethodBase {

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
   * @internal param \Drupal\commerce_shipping\PackageTypeManagerInterface $package_type_manager
   *   The package type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PackageTypeManagerInterface $packageTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $packageTypeManager);
    $this->packageTypeManager = $packageTypeManager;
    foreach ($this->pluginDefinition['services'] as $id => $label) {
      $this->services[$id] = new ShippingService($id, (string) $label);
    }
    $this->setConfiguration($configuration);
  }

  /**
   *
   */
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
      'testMode' => '',
      'nRate' => '',
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
    $form['testMode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Test Mode'),
    ];

    $form['nRate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Negotiated Rates'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

    parent::validateConfigurationForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {

      $values = $form_state->getValue($form['#parents']);

      $this->configuration['access_key'] = $values['access_key'];
      $this->configuration['user_id'] = $values['user_id'];
      $this->configuration['password'] = $values['password'];
      $this->configuration['testMode'] = $values['testMode'];
      $this->configuration['nRate'] = $values['nRate'];

    }
    parent::submitConfigurationForm($form, $form_state);
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
    // Rates Array.
    $rates = [];

    if ($shipment->getShippingProfile()->get('address')->isEmpty()) {
      $rates = [];
    }
    else {
      // @todo Make that class a service.
      $ups = new Ups($this->configuration);
        $UpsRates = $ups->getUpsRate($shipment);
        foreach ($UpsRates as $upsRateObject) {
          foreach ($upsRateObject as $upsRate) {
            $cost = $upsRate->TotalCharges->MonetaryValue;
            $currency = $upsRate->TotalCharges->CurrencyCode;

            $price = new Price((string) $cost, $currency);
            $ServiceCode = $upsRate->Service->getCode();
            $ServiceName = $upsRate->Service->getName();

            $shippingService = new ShippingService(
              $ServiceName,
              $ups->translateServiceCodeToString($ServiceCode)
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

