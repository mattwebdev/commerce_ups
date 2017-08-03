<?php
namespace Drupal\commerce_ups\Services;

use Psy\Exception\Exception;
use Ups\AddressValidation;
use Ups\Entity\Address;

class UPSAddress {

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
   * @return \Exception|\Psy\Exception\Exception|\stdClass|\Ups\Entity\AddressValidationResponse
   */
  public function verifyAddress(Address $address, $configuration) {
    $validation = new AddressValidation($configuration['accessKey'], $configuration['userId'], $configuration['password']);
    try {
      // @todo remove 2nd and 3rd parameter.
      $response = $validation->validate($address, $requestOption = AddressValidation::REQUEST_OPTION_ADDRESS_VALIDATION, $maxSuggestion = 15);
    } catch (Exception $e) {
      $response = $e;
    }

    return $response;
  }

}
