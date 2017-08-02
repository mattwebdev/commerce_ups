<?php
namespace Drupal\commerce_ups\Services;

use Drupal\physical\Dimensions;
use Drupal\physical\Weight;
use Ups\Entity\Dimensions as PackageDimensions;
use Ups\Entity\PackageWeight;
use Ups\Entity\UnitOfMeasurement;

class UPSPhysical {
  public function getWeight(Weight $weight) {
    $UPSWeight = new PackageWeight();
    $UPSWeight->setUnitOfMeasurement($this->translateWeightUnits($weight));
    $UPSWeight->setWeight($weight->getNumber());

    return $UPSWeight;

  }

  public function getDimensions(Dimensions $dimensions) {
    $UPSDimensions = new PackageDimensions();
    $UPSDimensions->setUnitOfMeasurement($this->translateUnitOfMeasurement($dimensions));
    $UPSDimensions->setHeight($dimensions->getHeight());
    $UPSDimensions->setLength($dimensions->getLength());
    $UPSDimensions->setWidth($dimensions->getWidth());

    return $UPSDimensions;
  }

  public function translateUnitOfMeasurement(Dimensions $dimensions) {
    $UPSUnit = new UnitOfMeasurement();
    $Unit = $dimensions->getUnit();
    $UPSUnit->setCode($Unit);
    return $UPSUnit;
  }

  public function translateWeightUnits(Weight $weight) {
    $UPSWeightUnit = new UnitOfMeasurement();
    $weightUnit = $weight->getUnit();
    $UPSWeightUnit->setCode($weightUnit);
    return $UPSWeightUnit;
  }
}