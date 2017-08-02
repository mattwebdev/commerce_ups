<?php

namespace Drupal\commerce_ups\Controller;

class UPShelpers {

  public function getUpsShippingPrice($db, $method, $psize, $from, $to, $weight, $error_message) {
    global $error_message;

    //	require_once($settings["GlobalServerPath"]."/content/engine/engine_ups.php");
    $ups = new FYPUPS;
    //	$ups->setServer("https://wwwcie.ups.com/ups.app/xml/Rate");
    $ups->setServer("https://www.ups.com/ups.app/xml/Rate");

    $ups->setResidential("1");
    $ups->setNegotiatedRates("1");
    $ups->setPickupType("01");
    $ups->setWeightUnit("LBS");
    $ups->setLengthUnit("IN");
    $dims = explode("x", $psize);
    if ($dims[0]) {
      $ups->setLength($dims[0]);
    }
    if ($dims[1]) {
      $ups->setWidth($dims[1]);
    }
    if ($dims[2]) {
      $ups->setHeight($dims[2]);
    }
    $ups->setContainer("00");
    $ups->setCountry($to["country"], "US");

    $ups->setDestAddress($to["address"]);
    $ups->setDestCity($to["city"]);
    $ups->setDestState($to["state"]);
    $ups->setDestZip($to["zip"]);
    $ups->setOrigAddress($from["address"]);
    $ups->setOrigCity($from["city"]);
    $ups->setOrigState($from["state"]);
    $ups->setOrigZip($from["zip"]);

    $ups->setWeight($weight);
    $ups->setService($method);

    $price = $ups->getShippingRate();
    if ($method != "") {
      if ($price && $price > 0) {
        return $price;
      }
      else {
        //		$error_message = "Please choose other shipping service.<br>We're so sorry, but selected one is not available for your order.";
        return FALSE;
      }
    }
    else {
      //echo "<pre>";
      //print_r($price);
      //echo "</pre>";
      //exit;

      return $price;
    }

  }

  public function getTimeInTransit($from, $to, $ups_pickupdate) {
    $ups = new FYPUPS;
    $ups->setCountry($to["country"], "US");
    $ups->setDestZip($to["zip"]);
    $ups->setOrigAddress($from["address"]);
    $ups->setOrigCity($from["city"]);
    $ups->setOrigState($from["state"]);
    $ups->setOrigZip($from["zip"]);
    $a = $ups->getTimeInTransit($ups_pickupdate);
    if (isset($a['ServiceSummary'])) {
      foreach ($a['ServiceSummary'] as $a2) {
        if (isset($a2['Service'][0]['Code'][0]) && $a2['Service'][0]['Code'][0] == "GND") {
          $this->dest_city = (isset($a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision2'][0]) ? $a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision2'][0] : NULL);
          $this->dest_state = (isset($a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision1'][0]) ? $a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision1'][0] : NULL);
          return ((isset($a2['EstimatedArrival'][0]['BusinessTransitDays'][0]) ? $a2['EstimatedArrival'][0]['BusinessTransitDays'][0] : FALSE));
        }
      }
      return (FALSE);
    }
    return (FALSE);
  }

  public function getServiceTimeInTransit($from, $to, $ups_pickupdate) {
    //echo "<pre>Services: ";
    $ups = new FYPUPS;
    $ups->setCountry($to["country"], "US");
    $ups->setDestZip($to["zip"]);
    $ups->setOrigAddress($from["address"]);
    $ups->setOrigCity($from["city"]);
    $ups->setOrigState($from["state"]);
    $ups->setOrigZip($from["zip"]);

    /*
     * Array (
        [PickupDate] => Array (
                [0] => 2016-09-06
            )
        [TransitFrom] => Array (
                [0] => Array (
                        [AddressArtifactFormat] => Array (
                                [0] => Array (
                                        [PoliticalDivision2] => Array (
                                                [0] => SKOKIE
                                            )
                                        [PoliticalDivision1] => Array (
                                                [0] => IL
                                            )
                                        [Country] => Array (
                                                [0] => UNITED STATES
                                            )
                                        [CountryCode] => Array (
                                                [0] => US
                                            )
                                        [PostcodePrimaryLow] => Array (
                                                [0] => 60076
                                            )
                                    )
                            )
                    )
            )
        [TransitTo] => Array (
                [0] => Array (
                        [AddressArtifactFormat] => Array (
                                [0] => Array (
                                        [PoliticalDivision2] => Array (
                                                [0] => PORT ORANGE
                                            )
                                        [PoliticalDivision1] => Array (
                                                [0] => FL
                                            )
                                        [Country] => Array (
                                                [0] => UNITED STATES
                                            )
                                        [CountryCode] => Array (
                                                [0] => US
                                            )
                                        [PostcodePrimaryLow] => Array (
                                                [0] => 32128
                                            )
                                    )
                            )
                    )
            )
        [Disclaimer] => Array (
                [0] => Services listed as guaranteed are backed by a money-back guarantee for transportation charges only. UPS guarantees the day of delivery for every ground package you ship to any address within all 50 states and Puerto Rico. See Terms and Conditions in the Service Guide for details.
            )
        [ServiceSummary] => Array (
                [0] => Array (
                        [Service] => Array (
                                [0] => Array (
                                        [Code] => Array (
                                                [0] => 1DM
                                            )
                                        [Description] => Array (
                                                [0] => UPS Next Day Air Early
                                            )
                                    )
                            )
                        [Guaranteed] => Array (
                                [0] => Array (
                                        [Code] => Array (
                                                [0] => Y
                                            )
                                    )
                            )
                        [EstimatedArrival] => Array (
                                [0] => Array (
                                        [BusinessTransitDays] => Array (
                                                [0] => 1
                                            )
                                        [Time] => Array (
                                                [0] => 10:00:00
                                            )
                                        [PickupDate] => Array (
                                                [0] => 2016-09-06
                                            )
                                        [PickupTime] => Array (
                                                [0] => 21:00:00
                                            )
                                        [Date] => Array (
                                                [0] => 2016-09-07
                                            )
                                        [DayOfWeek] => Array (
                                                [0] => WED
                                            )
                                        [CustomerCenterCutoff] => Array (
                                                [0] => 20:00:00
                                            )
                                    )
                            )
                    )
     */
    $a = $ups->getTimeInTransit($ups_pickupdate);
    if (isset($a['ServiceSummary'])) {
      $servicesArray = [];
      foreach ($a['ServiceSummary'] as $a2) {
        if (isset($a2['Service'][0]['Code'][0]) && $a2['Service'][0]['Code'][0] != 29) {        // each service - skip WWX Freight
          $this->dest_city = (isset($a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision2'][0]) ? $a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision2'][0] : NULL);
          $this->dest_state = (isset($a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision1'][0]) ? $a['TransitTo'][0]['AddressArtifactFormat'][0]['PoliticalDivision1'][0] : NULL);
          $serviceArray = [];
          $serviceArray['Description'] = $a2['Service'][0]['Description'][0];
          $serviceArray['Days'] = $a2['EstimatedArrival'][0]['BusinessTransitDays'][0];
          $serviceArray['DelDay'] = $a2['EstimatedArrival'][0]['DayOfWeek'][0];
          $serviceArray['DelDate'] = $a2['EstimatedArrival'][0]['Date'][0];
          $servicesArray[$a2['Service'][0]['Code'][0]] = $serviceArray;
        }
      }
      //echo "<pre>";
      //print_r($a);
      //echo "</pre>";
      //exit;
      return ($servicesArray);
    }
    return (FALSE);
  }

  public function getAddressValidation($to) {
    $ups = new FYPUPS;
    $ups->setDestCity($to["city"]);
    $ups->setDestZip($to["zip"]);
    $ups->setDestState($to["state"]);
    $addressData = $ups->getAddressVerification();
    return ($addressData);
  }

  public function getStreetAddressValidation($to) {
    $ups = new FYPUPS;
    $ups->setDestCompany($to["businessName"]);
    $ups->setDestContact($to["contactName"]);
    $ups->setDestAddress($to["address"]);
    $ups->setDestAddress2($to["address2"]);
    $ups->setDestCity($to["city"]);
    $ups->setDestZip($to["zip"]);
    $ups->setDestState($to["state"]);
    $ups->setCountry($to["country"], "US");
    $addressData = $ups->getStreetAddressVerification();
    return ($addressData);
  }

  public function getTrackingInfo($trackingNo) {
    $ups = new FYPUPS;
    $trackingData = $ups->getTrackingInfo($trackingNo);
    return ($trackingData);
  }

}
