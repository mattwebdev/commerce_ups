<?php
namespace Drupal\commerce_ups\Services;

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

}