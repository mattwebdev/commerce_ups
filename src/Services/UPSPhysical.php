<?php
namespace Drupal\commerce_ups\Services;
use Drupal\physical\Measurement;
use Drupal\physical\Weight;
use Ups\Entity\Dimensions as PackageDimensions;
use Ups\Entity\PackageWeight;
use Ups\Entity\UnitOfMeasurement;

class UPSPhysical {

  /**
   * @param \Drupal\physical\Weight $weight
   *
   * @return \Ups\Entity\PackageWeight
   */
  public function getWeight(Weight $weight) {
    $UPSWeight = new PackageWeight();
    $UPSWeight->setUnitOfMeasurement($this->translateWeightUnits($weight));
    $UPSWeight->setWeight($weight->getNumber());

    return $UPSWeight;

  }

  /**
   * @param \Drupal\physical\Measurement $height
   * @param \Drupal\physical\Measurement $length
   * @param \Drupal\physical\Measurement $width
   *
   * @return \Ups\Entity\Dimensions
   */
  public function getDimensions(Measurement $height, Measurement $length, Measurement $width) {
    $UPSDimensions = new PackageDimensions();
    $UPSDimensions->setUnitOfMeasurement($this->translateUnitOfMeasurement($height,$length,$width));
    $UPSDimensions->setHeight($height->getNumber());
    $UPSDimensions->setLength($length->getNumber());
    $UPSDimensions->setWidth($width->getNumber());

    return $UPSDimensions;
  }

  /**
   * @param \Drupal\physical\Measurement $height
   * @param \Drupal\physical\Measurement $length
   * @param \Drupal\physical\Measurement $width
   *
   * @return \Ups\Entity\UnitOfMeasurement
   *
   */
  public function translateUnitOfMeasurement(Measurement $height, Measurement $length, Measurement $width) {
    $equality = $this->checkUnitEquality($height,$length,$width);
    $UPSUnit = new UnitOfMeasurement();
    if($equality == 0) {
      $UPSUnit->setCode($height->getUnit());
    } else {
      \Drupal::logger('commerce_ups')->warning("Units are not equal, using Height as the basic unit");
    }

    return $UPSUnit;
  }

  /**
   * @param \Drupal\physical\Weight $weight
   *
   * @return \Ups\Entity\UnitOfMeasurement
   */
  public function translateWeightUnits(Weight $weight) {
    $UPSWeightUnit = new UnitOfMeasurement();
    $weightUnit = $weight->getUnit();
    $UPSWeightUnit->setCode($weightUnit);
    return $UPSWeightUnit;
  }

  protected function checkUnitEquality(Measurement $height, Measurement $length, Measurement $width) {
    if(($height->getUnit() == $length->getUnit()) && ($length->getUnit() == $width->getUnit())) {
      return 0;
    } else {
      return 1;
    }
  }
}
