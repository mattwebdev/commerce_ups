<?php
namespace Drupal\commerce_ups\Services;

use Ups\Entity\Dimensions;
use Ups\Entity\Package;
use Ups\Entity\PackageWeight;
use Ups\Entity\PackagingType;

class UPSPackage {
  public function __construct(PackagingType $packageType, Dimensions $dimensions, PackageWeight $weight) {
    $package = new Package();
    $package->setPackagingType($packageType);
    $package->setDimensions($dimensions);
    $package->setPackageWeight($weight);
  }
}