<?php
namespace Drupal\commerce_ups\Services;

use Ups\Entity\Dimensions as PackageDimensions;
use Ups\Entity\Package;
use Ups\Entity\PackageWeight;
use Ups\Entity\PackagingType;

// @todo any reason to use this versus manually building object elsewhere?
// @todo is this used?
// @todo this shouldn't be marked as a service, since it has dynamic non-service constructor.
class UPSPackage {

  /**
   * UPSPackage constructor.
   *
   * @param \Ups\Entity\PackagingType $packageType
   * @param \Ups\Entity\Dimensions $dimensions
   * @param \Ups\Entity\PackageWeight $weight
   */
  public function __construct(PackagingType $packageType, PackageDimensions $dimensions, PackageWeight $weight) {
    $package = new Package();
    $package->setPackagingType($packageType);
    $package->setDimensions($dimensions);
    $package->setPackageWeight($weight);
  }

  public function getPackagingType() {

  }
}
