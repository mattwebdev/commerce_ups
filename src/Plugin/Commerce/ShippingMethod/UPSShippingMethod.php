<?php

namespace Drupal\commerce_ups\Plugin\CommerceShippingMethod;

use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_sitemap\Logger;

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
  public function defaultConfiguration() {
    return [
        'access_key' => NULL,
        'user_id' => NULL,
        'password' => NULL,
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
    $accessKey = $this->configuration['access_key'];
    $userId = $this->configuration['user_id'];
    $password = $this->configuration['password'];
    //rates is always an empty array to start
    $rates = [];
    if ($shipment->getShippingProfile()->address->isEmpty()) {
    }
    else {
    }
    return $rates;

  }

}
