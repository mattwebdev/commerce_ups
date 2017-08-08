<?php

namespace Drupal\commerce_ups\Services;

use Exception;
use Ups\AddressValidation;
use Ups\Entity\Address;

/**
 *
 */
class UpsAddress {

  /**
   *
   */
  public function __construct($addressLine1, $addressLine2, $city, $state, $zip, $country) {
    $address = new Address();
    $address->setAddressLine1($addressLine1);
    $address->setAddressLine2($addressLine2);
    $address->setCity($city);
    $address->setStateProvinceCode($state);
    $address->setPostalCode($zip);
    $address->setCountryCode($country);

    return $address;
  }

  /**
   * @param \Ups\Entity\Address $address
   * @param $configuration
   *
   * @return \Exception | AddressValidation
   */
  public function verifyAddress(Address $address, $configuration) {
    $validation = new AddressValidation($configuration['accessKey'], $configuration['userId'], $configuration['password']);
    try {
      $response = $validation->validate($address);
    }
    catch (Exception $e) {
      $response = $e;
    }

    return $response;
  }

}
