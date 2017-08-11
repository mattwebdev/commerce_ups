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
   * @param $code
   *
   * @return \Ups\Entity\UnitOfMeasurement
   */
  public function setUnitOfMeasurement($code) {
    $upsUnit = new UnitOfMeasurement();
    $upsUnit->setCode($code);
    return $upsUnit;
  }

  /**
   * Convert commerce UOM to UPS API UOM.
   * @param $unit
   * @return string
   */
  public function getUnitOfMeasure($unit) {
    // todo: map all required units.
    switch ($unit) {
      case 'lb':
        return UnitOfMeasurement::PROD_POUNDS;
      case 'kg':
        return UnitOfMeasurement::PROD_KILOGRAMS;
      case 'in':
        return UnitOfMeasurement::UOM_IN;
      case 'cm':
        return UnitOfMeasurement::UOM_CM;
    }
    return $unit;
  }

}
