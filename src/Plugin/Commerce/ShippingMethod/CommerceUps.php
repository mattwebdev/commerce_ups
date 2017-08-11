<?php

namespace Drupal\commerce_ups\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_ups\UPSRateRequest;


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
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_information' => [
        'access_key' => '',
        'user_id' => '',
        'password' => '',
        'mode' => 'test',
      ],
      'options' => [
        'log' => [],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['api_information'] = [
      '#type' => 'details',
      '#title' => $this->t('API information'),
      '#description' => $this->isConfigured() ? $this->t('Update your UPS API information.') : $this->t('Fill in your UPS API information.'),
      '#weight' => $this->isConfigured() ? 10 : -10,
      '#open' => !$this->isConfigured(),
    ];

    $form['api_information']['access_key'] = [
      '#type' => 'textfield',
      '#title' => t('Access Key'),
      '#description' => t(''),
      '#default_value' => $this->configuration['api_information']['access_key'],
      '#required' => TRUE,
    ];
    $form['api_information']['user_id'] = [
      '#type' => 'textfield',
      '#title' => t('User ID'),
      '#description' => t(''),
      '#default_value' => $this->configuration['api_information']['user_id'],
      '#required' => TRUE,
    ];
    $form['api_information']['password'] = [
      '#type' => 'password',
      '#title' => t('Password'),
      '#description' => t(''),
      '#default_value' => '',
      '#required' => TRUE,
    ];
    $form['api_information']['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#description' => $this->t('Choose whether to use the test or live mode.'),
      '#options' => [
        'test' => $this->t('Test'),
        'live' => $this->t('Live'),
      ],
      '#default_value' => $this->configuration['api_information']['mode'],
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('UPS Options'),
      '#description' => $this->t('Additional options for UPS'),
    ];
    $form['options']['log'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Log the following messages for debugging'),
      '#options' => [
        'request' => $this->t('API request messages'),
        'response' => $this->t('API response messages'),
      ],
      '#default_value' => $this->configuration['options']['log'],
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

      $this->configuration['api_information']['access_key'] = $values['api_information']['access_key'];
      $this->configuration['api_information']['user_id'] = $values['api_information']['user_id'];
      if (!empty($values['api_information']['password'])) {
        $this->configuration['api_information']['password'] = $values['api_information']['password'];
      }
      $this->configuration['api_information']['mode'] = $values['api_information']['mode'];

      $this->configuration['options']['packaging'] = $values['options']['packaging'];
      $this->configuration['options']['log'] = $values['options']['log'];

    }
    parent::submitConfigurationForm($form, $form_state);
  }


  /**
   * Gets the shipping method label.
   *
   * @return mixed
   *   The shipping method label.
   */
  public function getLabel() {

  }

  /**
   * Gets the default package type.
   *
   * @return \Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageTypeInterface
   *   The default package type.
   */
  public function getDefaultPackageType() {

  }


  public function calculateRates(ShipmentInterface $shipment){
    $rate_request = new UPSRateRequest($this->configuration, $shipment);
    $ShoppedRates = $rate_request->getRates();
    $rates = [];
    if ($shipment->getShippingProfile()->get('address')->isEmpty()) {
      $rates = [];
    }
    else {
      // @todo Make that class a service.
      foreach ($ShoppedRates as $upsRateObject) {
        foreach ($upsRateObject as $upsRate) {
          $cost = $upsRate->TotalCharges->MonetaryValue;
          $currency = $upsRate->TotalCharges->CurrencyCode;
          $price = new Price((string) $cost, $currency);
          $serviceCode = $upsRate->Service->getCode();
          $serviceName = $upsRate->Service->getName();
          $shippingService = new ShippingService(
            $serviceName,
            $serviceCode
          );
          $rates[] = new ShippingRate(
            $serviceCode,
            $shippingService,
            $price
          );
        }
      }
    }
    return $rates;
  }

  /**
   * Selects the given shipping rate for the given shipment.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   * @param \Drupal\commerce_shipping\ShippingRate $rate
   *   The shipping rate.
   */
  public function selectRate(ShipmentInterface $shipment, ShippingRate $rate) {

  }

  /**
   * Determine if we have the minimum information to connect to UPS.
   *
   * @return bool
   *   TRUE if there is enough information to connect, FALSE otherwise.
   */
  protected function isConfigured() {
    $api_information = $this->configuration['api_information'];

    return (
      !empty($api_information['access_key'])
      && !empty($api_information['user_id'])
      && !empty($api_information['password'])
    );
  }
}

