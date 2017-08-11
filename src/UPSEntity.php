<?php

namespace Drupal\commerce_ups;

use Ups\Entity\UnitOfMeasurement;

class UPSEntity {
  /**
   * UPSEntity constructor.
   */
  public function __construct() {
  }

  /**
   * Convert commerce UOM to UPS API UOM.
   * @param $unit
   * @return string
   */
  public function getUnitOfMeasure($unit) {
    // todo: map all required units.
    switch ($unit) {
      case 'LBS':
        return UnitOfMeasurement::PROD_POUNDS;
      case 'KGS':
        return UnitOfMeasurement::PROD_KILOGRAMS;
      case 'IN':
        return UnitOfMeasurement::UOM_IN;
      case 'CM':
        return UnitOfMeasurement::UOM_CM;
    }
    return $unit;
  }

}
