<?php

namespace Drupal\commerce_ups\Services;

use Ups\Entity\Address;
use Ups\Entity\Shipper;

/**
 * @todo any reason to use this versus manual construction?
 */
class UpsShipper {

  /**
   * UpsShipper constructor.
   *
   * @param $name
   * @param $companyName
   * @param $phoneNumber
   * @param $faxNumber
   * @param $emailAddress
   * @param \Ups\Entity\Address $Address
   */
  public function __construct($name, $companyName, $phoneNumber, $faxNumber, $emailAddress, Address $Address) {

    $shipper = new Shipper();
    $shipper->setName($name);
    $shipper->setCompanyName($companyName);
    $shipper->setPhoneNumber($phoneNumber);
    $shipper->setFaxNumber($faxNumber);
    $shipper->setEmailAddress($emailAddress);
    $shipper->setAddress($Address);

  }

}
