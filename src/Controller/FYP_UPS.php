<?php

namespace Drupal\commerce_ups\Controller;

class FYPUPS {

  var $settings = [];

  var $server = "";

  var $tracking_server = "https://www.ups.com/ups.app/xml/Track";

  var $av_server = "https://onlinetools.ups.com/ups.app/xml/AV";

  var $xav_server = "https://onlinetools.ups.com/ups.app/xml/XAV";

  var $tnt_server = "https://www.ups.com/ups.app/xml/TimeInTransit";

  var $user = "rwhirn";

  var $pass = "mamafox";

  var $service = "";

  var $dest_company = "";

  var $dest_contact = "";

  var $dest_address = "";

  var $dest_address2 = "";

  var $dest_city = "";

  var $dest_state = "";

  var $dest_zip;

  var $orig_address = "";

  var $orig_state = "";

  var $orig_zip;

  var $shipper_name = "ForYourParty.com";

  var $shipper_number = "Y230X0";

  var $pounds;

  var $ounces;

  var $container = "None";

  var $size = "REGULAR";

  var $machinable;

  var $to_country;

  var $from_country;

  var $pickupType;

  var $accessKey = "8C41EFA06961B874";

  var $weightUnit = "LBS";

  var $lengthUnit = "IN";

  var $length;

  var $width;

  var $height;

  var $residential;

  var $negotiatedRates;

  var $customerClassification;

  function setServer($server) {
    $this->server = $server;
  }

  function setTrackingServer($server) {
    $this->tracking_server = $server;
  }

  function setAVServer($server) {
    $this->av_server = $server;
  }

  function setAccessKey($AccessKey) {
    $this->accessKey = $AccessKey;
  }

  function setUserName($user) {
    $this->user = $user;
  }

  function setShipperName($name) {
    $this->shipper_name = $name;
  }

  function setShipperNumber($number) {
    $this->shipper_number = $number;
  }

  function setPass($pass) {
    $this->pass = $pass;
  }

  function setService($service) {
    /*
        /RatingServiceSelectionRequest
        /Shipment
        /Service
        /Code
        The code for the UPS Service
        associated with the shipment.
        Valid domestic values:
        �14� = Next Day Air Early AM
        �01� = Next Day Air
        �13� = Next Day Air Saver
        �59� = 2nd Day Air AM
        �02� = 2nd Day Air
        �12� = 3 Day Select
        �03� = Ground
        Valid international values:
        �11�= Standard
        �07� = Worldwide Express
        �54� = Worldwide Express Plus
        �08� = Worldwide Expedited
        �65� = Saver


        '01' => 'Next Day Air',
        '02' => '2nd Day Air',
        '03' => 'Ground',
        '07' => 'Worldwide Express',
        '08' => 'Worldwide Expedited',
        '11' => 'Standard',
        '12' => '3 Day Select',
        '13' => 'Next Day Air Saver',
        '14' => 'Next Day Air Early A.M.',
        '54' => 'Worldwide Express Plus',
        '59' => '2nd Day Air A.M.',
        '65' => 'Worldwide Saver'

        1DMS �14� : Next Day Air Early AM (Saturday)
        1DAS �01� : Next Day Air (Saturday)
        1DM  �14� : Next Day Air Early AM
        1DA  �01� : Next Day Air
        1DP  �13� : Next Day Air Saver
        2DM  �59� : 2nd Day Air AM
        2DAS �02� : 2nd Day Air (Saturday)
        2DA  �02� : 2nd Day Air
        3DS  �12� : 3 Day Select
        GND  �03� : Ground
        EP   �54� : Worldwide Express Plus
        RET	EP   �21� : Worldwide Express Plus
        ES   �07� : Worldwide Express
        RET ES   �01� : Worldwide Express
        SV   �65� : Worldwide Saver (Express)
        RET	SV   �28� : Worldwide Saver (Express)
        EX   �08� : Worldwide Expedited
        RET	EX   �05� : Worldwide Expedited
        ST   �11� : Standard
        RET	ST   �03� : Standard
    */
    $this->service = $service;
  }

  function setPickupType($pickupType) {
    /*
    /RatingServiceSelectionRequest
    /PickupType
    /Code
    Default value is 01.
    Valid values are:
    01 � Daily Pickup
    03 � Customer Counter
    06 � One Time Pickup
    07 � On Call Air
    11 � Suggested Retail Rates
    19 � Letter Center
    20 � Air Service Center
    */
    $this->pickupType = $pickupType;
    if ($pickupType == 11) {
      $this->customerClassification = "<CustomerClassification>
       												<Code>04</Code>
   											</CustomerClassification>";
    }
    else {
      $this->customerClassification = "<CustomerClassification>
       												<Code>01</Code>
   											</CustomerClassification>";
    }
  }

  function setCountry($toCountry, $fromCountry) {
    $this->to_country = $toCountry;
    $this->from_country = $fromCountry;
  }

  function setDestCompany($sending_company) {
    $this->dest_company = trim($sending_company);
  }

  function setDestContact($sending_contact) {
    $this->dest_contact = trim($sending_contact);
  }

  function setDestAddress($sending_address) {
    $this->dest_address = trim($sending_address);
  }

  function setDestAddress2($sending_address2) {
    $this->dest_address2 = trim($sending_address2);
  }

  function setDestCity($sending_city) {
    /* req for countries without zips */
    $this->dest_city = trim($sending_city);
  }

  function setDestState($sending_state) {
    $this->dest_state = $sending_state;
  }

  function setDestZip($sending_zip) {
    /* Must be 5 digit zip (No extension) */
    $this->dest_zip = trim($sending_zip);
  }

  function setOrigAddress($orig_address) {
    $this->orig_address = $orig_address;
  }

  function setOrigCity($orig_city) {
    $this->orig_city = $orig_city;
  }

  function setOrigState($orig_state) {
    $this->orig_state = $orig_state;
  }

  function setOrigZip($orig_zip) {
    $this->orig_zip = $orig_zip;
  }

  function setWeight($pounds, $ounces = 0) {
    /* Must weight less than 150 lbs. */
    if ($pounds < 1 && $pounds > 0) {
      $pounds = 1;
    }
    $this->pounds = $pounds;
    $this->ounces = $ounces;
  }

  function setWeightUnit($weightUnit) {
    $this->weightUnit = $weightUnit;
  }

  function setLengthUnit($lengthUnit) {
    $this->lengthUnit = $lengthUnit;
  }

  function setLength($length) {
    $this->length = $length;
  }

  function setWidth($width) {
    $this->width = $width;
  }

  function setHeight($height) {
    $this->height = $height;
  }


  function setContainer($cont) {
    /*
    /RatingServiceSelectionRequest
    /Shipment
    /Package
    /PackagingType
    /Code
    The code for the UPS
    packaging type associated
    with the package.
    Valid values:�00� =	�UNKNOWN�
    �01� = UPS Letter
    �02� = Package
    �03� = Tube
    �04� = Pak
    �21� = Express Box
    �24� = 25KG Box
    �25� = 10KG Box
    �30� = Pallet
    �2a� = Small Express Box
    �2b� = Medium Express Box
    �2c� = Large Express Box
    */

    $this->container = '<PackagingType>' .
      '<Code>' . htmlspecialchars($cont, ENT_XML1) . '</Code>' .
      '</PackagingType>';
    if ($cont == "00") {
      $this->container .= '<Dimensions>' .
        '<UnitOfMeasurement>' .
        '<Code>' . htmlspecialchars($this->lengthUnit, ENT_XML1) . '</Code>' .
        '</UnitOfMeasurement>' .
        '<Length>' . htmlspecialchars($this->length, ENT_XML1) . '</Length>' .
        '<Width>' . htmlspecialchars($this->width, ENT_XML1) . '</Width>' .
        '<Height>' . htmlspecialchars($this->height, ENT_XML1) . '</Height>' .
        '</Dimensions>';

    }
  }

  function setResidential($flag) {
    If ($flag > 0) {
      $this->residential = '<ResidentialAddressIndicator />';
    }

  }

  function setNegotiatedRates($flag) {
    If ($flag > 0) {
      $this->negotiatedRates = '<RateInformation>' .
        '<NegotiatedRatesIndicator />' .
        '</RateInformation>';
    }

  }

  function setSaturday($flag) {
    If ($flag > 0) {
      $this->negotiatedRates .= '<ShipmentServiceOptions>' .
        '<SaturdayDelivery />' .
        '</ShipmentServiceOptions>';
    }

  }

  function setSize($size) {
    /* Valid Sizes
    */
    $this->size = $size;
  }

  function getShippingRate() {
    global $billable_weight, $pub_price, $commercial, $error_message, $settings;
    // may need to urlencode xml portion
    $str = '<?xml version="1.0"?>' .
      '<AccessRequest xml:lang="en-US">' .
      '<AccessLicenseNumber>' . htmlspecialchars($this->accessKey, ENT_XML1) . '</AccessLicenseNumber>' .
      '<UserId>' . htmlspecialchars($this->user, ENT_XML1) . '</UserId>' .
      '<Password>' . htmlspecialchars($this->pass, ENT_XML1) . '</Password>' .
      '</AccessRequest>' .
      '<?xml version="1.0"?>' .
      '<RatingServiceSelectionRequest xml:lang="en-US">' .
      '<Request>' .
      '<TransactionReference>' .
      '<CustomerContext>FYP Rate Request</CustomerContext>' .
      '<XpciVersion>1.0001</XpciVersion>' .
      '</TransactionReference>' .
      '<RequestAction>Rate</RequestAction>' .
      '<RequestOption>' . (($this->service == "") ? 'Shop' : 'Rate') . '</RequestOption>' .
      '</Request>' .
      '<PickupType>' .
      '<Code>' . htmlspecialchars($this->pickupType, ENT_XML1) . '</Code>' .
      '</PickupType>' .
      '<Shipment>' . $this->negotiatedRates .
      '<Shipper>' .
      '<Name>' . htmlspecialchars($this->shipper_name, ENT_XML1) . '</Name>' .
      '<ShipperNumber>' . htmlspecialchars($this->shipper_number, ENT_XML1) . '</ShipperNumber>' .
      '<Address>' .
      '<AddressLine1>' . htmlspecialchars($this->orig_address, ENT_XML1) . '</AddressLine1>' .
      '<StateProvinceCode>' . htmlspecialchars($this->orig_state, ENT_XML1) . '</StateProvinceCode>' .
      '<PostalCode>' . htmlspecialchars($this->orig_zip, ENT_XML1) . '</PostalCode>' .
      '<CountryCode>' . htmlspecialchars($this->from_country, ENT_XML1) . '</CountryCode>' .
      '</Address>' .
      '</Shipper>' .
      '<ShipTo>' .
      '<Address>' .
      '<AddressLine1>' . htmlspecialchars($this->dest_address, ENT_XML1) . '</AddressLine1>' .
      '<City>' . htmlspecialchars($this->dest_city, ENT_XML1) . '</City>' .
      '<StateProvinceCode>' . htmlspecialchars($this->dest_state, ENT_XML1) . '</StateProvinceCode>' .
      '<PostalCode>' . htmlspecialchars($this->dest_zip, ENT_XML1) . '</PostalCode>' .
      '<CountryCode>' . htmlspecialchars($this->to_country, ENT_XML1) . '</CountryCode>' .
      $this->residential .
      '</Address>' .
      '</ShipTo>' .
      '<ShipFrom>' .
      '<Address>' .
      '<AddressLine1>' . htmlspecialchars($this->orig_address, ENT_XML1) . '</AddressLine1>' .
      '<StateProvinceCode>' . htmlspecialchars($this->orig_state, ENT_XML1) . '</StateProvinceCode>' .
      '<PostalCode>' . htmlspecialchars($this->orig_zip, ENT_XML1) . '</PostalCode>' .
      '<CountryCode>' . htmlspecialchars($this->from_country, ENT_XML1) . '</CountryCode>' .
      '</Address>' .
      '</ShipFrom>' .
      '<Service>' .
      '<Code>' . htmlspecialchars($this->service, ENT_XML1) . '</Code>' .
      '</Service>' .
      '<Package>' .
      $this->container .
      '<PackageWeight>' .
      '<UnitOfMeasurement>' .
      '<Code>' . htmlspecialchars($this->weightUnit, ENT_XML1) . '</Code>' .
      '</UnitOfMeasurement>' .
      '<Weight>' . htmlspecialchars($this->pounds, ENT_XML1) . '</Weight>' .
      '</PackageWeight>' .
      '</Package>' .
      '</Shipment>' . $this->customerClassification .
      '</RatingServiceSelectionRequest>';

    //echo "<pre><b>UPS Request:</b><br>".htmlspecialchars($str)."</pre>";
    $buffer = "";
    $c = curl_init();

    //		if($settings['ProxyAvailable'] == "YES"){
    //			curl_setopt($c, CURLOPT_VERBOSE, 1);
    //			if(defined("CURLOPT_PROXYTYPE") && defined("CURLPROXY_HTTP") && defined("CURLPROXY_SOCKS5")){
    //				curl_setopt($c, CURLOPT_PROXYTYPE, $settings['ProxyType'] == "HTTP" ? CURLPROXY_HTTP : CURLPROXY_SOCKS5);
    //			}
    //			curl_setopt($c, CURLOPT_PROXY, $settings['ProxyAddress'].":".$settings['ProxyPort']);
    //			if($settings['ProxyRequiresAuthorization'] == "YES"){
    //				curl_setopt($c, CURLOPT_PROXYUSERPWD, $settings['ProxyUsername'].":".$settings['ProxyPassword']);
    //			}
    //			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
    //			curl_setopt($c, CURLOPT_TIMEOUT, 120);
    //		}

    curl_setopt($c, CURLOPT_URL, $this->server);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_POSTFIELDS, $str);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    if (!ini_get('safe_mode')) {
      set_time_limit(3000);
    }

    for ($loop = 3; $loop > 0; $loop--) {
      $buffer = curl_exec($c);
      if ($buffer !== FALSE) {
        break;
      }
      error_log("UPS Get Shipping Rate returned false! Retrying.");
      sleep(1);
    }

    //echo "<pre><b>UPS answer:</b><br>".htmlspecialchars($buffer )."</pre>";
    if ($buffer) {
      //			require_once($settings['GlobalServerPath']."/content/classes/class.xml.php");
      $xmlParser = new xmlToArrayParser();

      $data = $xmlParser->parse($buffer);

      //echo "<pre>";
      //print_r($data);
      //echo "</pre>";

      if (isset($data['RatingServiceSelectionResponse'][0]['Response'][0]['ResponseStatusDescription'][0]) && $data['RatingServiceSelectionResponse'][0]['Response'][0]['ResponseStatusDescription'][0] == "Success") {
        $a = $data['RatingServiceSelectionResponse'][0]['RatedShipment'][0];
        $code = $a['Service'][0]['Code'][0];
        if ($code == $this->service) {
          $pub_price = $a['TotalCharges'][0]['MonetaryValue'][0];
          if (isset($a['NegotiatedRates']) && isset($a['NegotiatedRates'][0]['NetSummaryCharges'][0]['GrandTotal'][0]['MonetaryValue'][0])) {
            $ship_rate = $a['NegotiatedRates'][0]['NetSummaryCharges'][0]['GrandTotal'][0]['MonetaryValue'][0];
          }
          else {
            $ship_rate = $pub_price;
          }
          $billable_weight = $a['BillingWeight'][0]['Weight'][0];
          $commercial = FALSE;
          if (isset($a['RatedShipmentWarning']) && count($a['RatedShipmentWarning']) > 1 && strpos($a['RatedShipmentWarning'][1],
              "Residential to Commercial")
          ) {
            $commercial = TRUE;
          }
        }
        else {
          $ship_rate = [];
          foreach ($data['RatingServiceSelectionResponse'][0]['RatedShipment'] as $a) {
            $pub_price = $a['TotalCharges'][0]['MonetaryValue'][0];
            if (isset($a['NegotiatedRates']) && isset($a['NegotiatedRates'][0]['NetSummaryCharges'][0]['GrandTotal'][0]['MonetaryValue'][0])) {
              $pub_price = $a['NegotiatedRates'][0]['NetSummaryCharges'][0]['GrandTotal'][0]['MonetaryValue'][0];
            }
            $ship_rate[$a['Service'][0]['Code'][0]] = $pub_price;
          }
        }
        //echo "<pre>";
        //print_r($data['RatingServiceSelectionResponse'][0]['RatedShipment']);
        //echo "</pre>";
        //exit;
        return ($ship_rate);
      }
      else {
        $error_message = "<br />" . $data['RatingServiceSelectionResponse'][0]['Response'][0]['Error'][0]['ErrorDescription'][0];
        error_log("UPS Get Shipping Rate returned invalid data! $error_message");
        $errorLogFile = @fopen("ShipError.log", "a");
        fwrite($errorLogFile, sprintf("%s \n%s\n", date("D M j G:i:s T Y"), print_r($data, TRUE)));
        fclose($errorLogFile);
        return (FALSE);
      }
    }
    else {
      error_log("UPS Get Shipping Rate returned no data! $buffer");
      return (FALSE);
    }
  }

  function getTrackingInfo($trackingNo) {
    global $settings;
    $str = '<?xml version="1.0"?>' .
      '<AccessRequest xml:lang="en-US">' .
      '<AccessLicenseNumber>' . htmlspecialchars($this->accessKey, ENT_XML1) . '</AccessLicenseNumber>' .
      '<UserId>' . htmlspecialchars($this->user, ENT_XML1) . '</UserId>' .
      '<Password>' . htmlspecialchars($this->pass, ENT_XML1) . '</Password>' .
      '</AccessRequest>' .
      '<?xml version="1.0"?>' .
      '<TrackRequest xml:lang="en-US">' .
      '<Request>' .
      '<TransactionReference>' .
      '<CustomerContext>FYP Track Request</CustomerContext>' .
      '<XpciVersion>1.0001</XpciVersion>' .
      '</TransactionReference>' .
      '<RequestAction>Track</RequestAction>' .
      '<RequestOption>activity</RequestOption>' .
      '</Request>' .
      '<ReferenceNumber><Value>' . htmlspecialchars($trackingNo, ENT_XML1) . '</Value></ReferenceNumber>' .
      '<ShipperNumber>' . htmlspecialchars($this->shipper_number, ENT_XML1) . '</ShipperNumber>' .
      '</TrackRequest>';
    //echo "<pre><b>UPS Request:</b><br>".htmlspecialchars($str)."</pre>".$this->tracking_server;

    $buffer = "";
    $c = curl_init();

    //		if($settings['ProxyAvailable'] == "YES"){
    //			curl_setopt($c, CURLOPT_VERBOSE, 1);
    //			if(defined("CURLOPT_PROXYTYPE") && defined("CURLPROXY_HTTP") && defined("CURLPROXY_SOCKS5")){
    //				curl_setopt($c, CURLOPT_PROXYTYPE, $settings['ProxyType'] == "HTTP" ? CURLPROXY_HTTP : CURLPROXY_SOCKS5);
    //			}
    //			curl_setopt($c, CURLOPT_PROXY, $settings['ProxyAddress'].":".$settings['ProxyPort']);
    //			if($settings['ProxyRequiresAuthorization'] == "YES"){
    //				curl_setopt($c, CURLOPT_PROXYUSERPWD, $settings['ProxyUsername'].":".$settings['ProxyPassword']);
    //			}
    //			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
    //			curl_setopt($c, CURLOPT_TIMEOUT, 120);
    //		}

    curl_setopt($c, CURLOPT_URL, $this->tracking_server);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_POSTFIELDS, $str);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    if (!ini_get('safe_mode')) {
      set_time_limit(3000);
    }
    $buffer = curl_exec($c);

    //echo "<pre><b>UPS answer:</b><br>".htmlspecialchars($buffer)."</pre>";
    //exit;

    if ($buffer) {
      //			require_once("content/classes/class.xml.php");
      $xmlParser = new xmlToArrayParser();
      $tracking_data = "";
      $data = $xmlParser->parse($buffer);
      //echo "<pre>";
      //print_r($data);
      //echo "</pre>";
      //exit;
      //die();

      if ($data['TrackResponse'][0]['Response'][0]['ResponseStatusCode'][0] == 1 and $data['TrackResponse'][0]['Response'][0]['ResponseStatusDescription'][0] == "Success") {
        $a = $data['TrackResponse'][0]['Shipment'][0];
        $tracking_data['shipping_method'] = $a['Service'][0]['Description'][0];
        $tracking_data['pickup_date'] = $a['PickupDate'][0];

        $tracking_data['pickup_date'] = date('m-d-Y', strtotime($tracking_data['pickup_date']));

        $tracking_data['scheduled_delivery_date'] = "";
        if (isset($a['ScheduledDeliveryDate'][0])) {
          $tracking_data['scheduled_delivery_date'] = date('m-d-Y',
            strtotime($a['ScheduledDeliveryDate'][0]));
          $tracking_data['scheduled_delivery_time'] = date('h:i A',
            strtotime($a['ScheduledDeliveryTime'][0]));
        }

        $tracking_data['tracking_number'] = $a['Package'][0]['TrackingNumber'][0];

        $tracking_data['rescheduled_delivery_date'] = "";
        if (isset($a['Package'][0]['RescheduledDeliveryDate'])) {
          $tracking_data['rescheduled_delivery_date'] = date('m-d-Y',
            strtotime($a['Package'][0]['RescheduledDeliveryDate'][0]));
        }

        foreach ($a['Package'][0]['Activity'] as $a2) {
          $t2 = "";
          $t2['status_type_code'] = $a2['Status'][0]['StatusType'][0]['Code'][0];
          $t2['status_type_description'] = ucfirst($a2['Status'][0]['StatusType'][0]['Description'][0]);

          $t2['status_code'] = $a2['Status'][0]['StatusCode'][0]['Code'][0];
          //$tracking_data['status_code']=$a['Package'][0]['Activity'][0]['Status'][0]['StatusCode'][0]['Code'][0];

          $t2['activity_location'] = "";
          $t2['activity_location1'] = "";
          if (isset($a2['ActivityLocation'][0])) {
            if (isset($a2['ActivityLocation'][0]['Address'][0])) {
              if (isset($a2['ActivityLocation'][0]['Address'][0]['AddressLine1'][0])) {
                $t2['activity_location'] .= ucfirst($a2['ActivityLocation'][0]['Address'][0]['AddressLine1'][0]);
              }
              if (isset($a2['ActivityLocation'][0]['Address'][0]['AddressLine2'][0])) {
                $t2['activity_location'] .= " " . ucfirst($a2['ActivityLocation'][0]['Address'][0]['AddressLine2'][0]);
              }
              if (isset($a2['ActivityLocation'][0]['Address'][0]['AddressLine3'][0])) {
                $t2['activity_location'] .= " " . ucfirst($a2['ActivityLocation'][0]['Address'][0]['AddressLine3'][0]);
              }
              if (isset($a2['ActivityLocation'][0]['Address'][0]['City'][0])) {
                $t2['activity_location1'] .= $a2['ActivityLocation'][0]['Address'][0]['City'][0];
              }
              if (isset($a2['ActivityLocation'][0]['Address'][0]['StateProvinceCode'][0])) {
                $t2['activity_location1'] .= " " . $a2['ActivityLocation'][0]['Address'][0]['StateProvinceCode'][0];
              }
              if (isset($a2['ActivityLocation'][0]['Address'][0]['PostalCode'][0])) {
                $t2['activity_location1'] .= " " . $a2['ActivityLocation'][0]['Address'][0]['PostalCode'][0];
              }
              if (isset($a2['ActivityLocation'][0]['Address'][0]['CountryCode'][0])) {
                $t2['activity_location1'] .= " " . $a2['ActivityLocation'][0]['Address'][0]['CountryCode'][0];
              }
            }
          }

          $t2['activity_date'] = date('m-d-Y', strtotime($a2['Date'][0]));
          $t2['activity_time'] = date('h:i A', strtotime($a2['Time'][0]));

          $tracking_data['SignedForByName'] = "";
          if (isset($a2['ActivityLocation'][0]['SignedForByName'][0])) {
            $tracking_data['SignedForByName'] = $a2['ActivityLocation'][0]['SignedForByName'][0];
          }

          $tracking_data['Activity'][] = $t2;
        }

        $tracking_data['message'] = "";
        if (isset($a['Package'][0]['Message'][0])) {
          $tracking_data['message'] = $tracking_data['status_code'] = $a['Package'][0]['Message'][0]['Description'][0];
        }

        $tracking_data['package_weight_unit'] = $a['Package'][0]['PackageWeight'][0]['UnitOfMeasurement'][0]['Code'][0];
        $tracking_data['package_weight'] = $a['Package'][0]['PackageWeight'][0]['Weight'][0];
        $tracking_data['shipment_weight_unit'] = $a['ShipmentWeight'][0]['UnitOfMeasurement'][0]['Code'][0];
        $tracking_data['shipment_weight'] = $a['ShipmentWeight'][0]['Weight'][0];

        return ($tracking_data);
      }
      else {
        return ($data);
      }
    }
    else {
      return (FALSE);
    }

  }

  function getAddressVerification() {
    global $settings;
    $str = '<?xml version="1.0"?>' .
      '<AccessRequest xml:lang="en-US">' .
      '<AccessLicenseNumber>' . htmlspecialchars($this->accessKey, ENT_XML1) . '</AccessLicenseNumber>' .
      '<UserId>' . htmlspecialchars($this->user, ENT_XML1) . '</UserId>' .
      '<Password>' . htmlspecialchars($this->pass, ENT_XML1) . '</Password>' .
      '</AccessRequest>' .
      '<?xml version="1.0"?>' .
      '<AddressValidationRequest xml:lang="en-US">' .
      '<Request>' .
      '<TransactionReference>' .
      '<CustomerContext>FYP AV Request</CustomerContext>' .
      '<XpciVersion>1.0001</XpciVersion>' .
      '</TransactionReference>' .
      '<RequestAction>AV</RequestAction>' .
      '</Request>' .
      '<Address>' .
      '<City>' . $this->dest_city . '</City>' .
      '<StateProvinceCode>' . htmlspecialchars($this->dest_state, ENT_XML1) . '</StateProvinceCode>' .
      '<PostalCode>' . htmlspecialchars($this->dest_zip, ENT_XML1) . '</PostalCode>' .
      '</Address>' .
      '</AddressValidationRequest>';
    //echo "<pre><b>UPS Request:</b><br>".htmlspecialchars($str)."</pre>".$this->av_server;

    $buffer = "";
    $c = curl_init();

    if ($settings['ProxyAvailable'] == "YES") {
      curl_setopt($c, CURLOPT_VERBOSE, 1);
      if (defined("CURLOPT_PROXYTYPE") && defined("CURLPROXY_HTTP") && defined("CURLPROXY_SOCKS5")) {
        curl_setopt($c, CURLOPT_PROXYTYPE,
          $settings['ProxyType'] == "HTTP" ? CURLPROXY_HTTP : CURLPROXY_SOCKS5);
      }
      curl_setopt($c, CURLOPT_PROXY, $settings['ProxyAddress'] . ":" . $settings['ProxyPort']);
      if ($settings['ProxyRequiresAuthorization'] == "YES") {
        curl_setopt($c, CURLOPT_PROXYUSERPWD, $settings['ProxyUsername'] . ":" . $settings['ProxyPassword']);
      }
      curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($c, CURLOPT_TIMEOUT, 120);
    }

    curl_setopt($c, CURLOPT_URL, $this->av_server);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_POSTFIELDS, $str);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    if (!ini_get('safe_mode')) {
      set_time_limit(3000);
    }
    $buffer = curl_exec($c);

    //echo "<pre><b>UPS answer:</b><br>".htmlspecialchars($buffer)."</pre>";


    if ($buffer) {
      //			require_once("content/classes/class.xml.php");
      $xmlParser = new xmlToArrayParser();
      $av_data = "";
      $data = $xmlParser->parse($buffer);
      //echo "<pre>";
      //print_r($data);
      //echo "</pre>";
      //exit;
      //die();

      if ($data['AddressValidationResponse'][0]['Response'][0]['ResponseStatusCode'][0] == 1 && $data['AddressValidationResponse'][0]['Response'][0]['ResponseStatusDescription'][0] == "Success") {
        $a = $data['AddressValidationResponse'][0]['AddressValidationResult'][0];
        $av_data['Rank'] = $a['Rank'][0];
        $av_data['Quality'] = $a['Quality'][0];
        $av_data['PostalCodeLowEnd'] = $a['PostalCodeLowEnd'][0];
        $av_data['PostalCodeHighEnd'] = $a['PostalCodeHighEnd'][0];
        $av_data['City'] = $a['Address'][0]['City'][0];
        $av_data['StateProvinceCode'] = $a['Address'][0]['StateProvinceCode'][0];

        return ($av_data);
      }
      else {
        return ($data);
      }
    }
    else {
      return (FALSE);
    }

  }

  function getStreetAddressVerification() {
    global $settings;
    $str = '<?xml version="1.0"?>' .
      '<AccessRequest xml:lang="en-US">' .
      '<AccessLicenseNumber>' . htmlspecialchars($this->accessKey, ENT_XML1) . '</AccessLicenseNumber>' .
      '<UserId>' . htmlspecialchars($this->user, ENT_XML1) . '</UserId>' .
      '<Password>' . htmlspecialchars($this->pass, ENT_XML1) . '</Password>' .
      '</AccessRequest>' .
      '<?xml version="1.0"?>' .
      '<AddressValidationRequest xml:lang="en-US">' .
      '<Request>' .
      '<TransactionReference>' .
      '<CustomerContext>FYP SLAV Request</CustomerContext>' .
      '<XpciVersion>1.0001</XpciVersion>' .
      '</TransactionReference>' .
      '<RequestAction>XAV</RequestAction>' .
      '<RequestOption>3</RequestOption>' .
      '</Request>' .
      '<MaximumListSize>3</MaximumListSize>' .
      '<AddressKeyFormat>' .
      '<ConsigneeName>' . $this->dest_contact . '</ConsigneeName>' .
      (($this->dest_company != NULL) ? '<BuildingName>' . $this->dest_company . '</BuildingName>' : "") .
      '<AddressLine>' . $this->dest_address . '</AddressLine>' .
      (($this->dest_address2 != NULL) ? '<AddressLine>' . $this->dest_address2 . '</AddressLine>' : "") .
      '<PoliticalDivision2>' . $this->dest_city . '</PoliticalDivision2>' .
      '<PoliticalDivision1>' . htmlspecialchars($this->dest_state, ENT_XML1) . '</PoliticalDivision1>' .
      '<PostcodePrimaryLow>' . htmlspecialchars($this->dest_zip, ENT_XML1) . '</PostcodePrimaryLow>' .
      '<CountryCode>' . htmlspecialchars($this->to_country, ENT_XML1) . '</CountryCode>' .
      '</AddressKeyFormat>' .
      '</AddressValidationRequest>';
    //echo "<pre><b>UPS Request:</b><br>".htmlspecialchars($str)."</pre>".$this->xav_server;

    $buffer = "";
    $c = curl_init();

    //		if($settings['ProxyAvailable'] == "YES"){
    //			curl_setopt($c, CURLOPT_VERBOSE, 1);
    //			if(defined("CURLOPT_PROXYTYPE") && defined("CURLPROXY_HTTP") && defined("CURLPROXY_SOCKS5")){
    //				curl_setopt($c, CURLOPT_PROXYTYPE, $settings['ProxyType'] == "HTTP" ? CURLPROXY_HTTP : CURLPROXY_SOCKS5);
    //			}
    //			curl_setopt($c, CURLOPT_PROXY, $settings['ProxyAddress'].":".$settings['ProxyPort']);
    //			if($settings['ProxyRequiresAuthorization'] == "YES"){
    //				curl_setopt($c, CURLOPT_PROXYUSERPWD, $settings['ProxyUsername'].":".$settings['ProxyPassword']);
    //			}
    //			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
    //			curl_setopt($c, CURLOPT_TIMEOUT, 120);
    //		}

    curl_setopt($c, CURLOPT_URL, $this->xav_server);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_POSTFIELDS, $str);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    if (!ini_get('safe_mode')) {
      set_time_limit(3000);
    }
    $buffer = curl_exec($c);

    //echo "<pre><b>UPS answer:</b><br>".htmlspecialchars($buffer)."</pre>";


    if ($buffer) {
      //header('Content-Type: application/xml');
      //print ($buffer);
      //exit;
      //			require_once("content/classes/class.xml.php");
      $av_data = "";
      $xmlParser = new xmlToArrayParser();
      $data = $xmlParser->parse($buffer);
      //echo "<pre>";
      //print_r($data);
      //exit;
      //die();

      //			if($data['AddressValidationResponse'][0]['Response'][0]['ResponseStatusCode'][0] == 1){ //&& $data['AddressValidationResponse'][0]['Response'][0]['ResponseStatusDescription'][0] == "Success"){
      //				if (isset($data['AddressValidationResponse'][0]['AmbiguousAddressIndicator']) && isset($data['AddressValidationResponse'][0]['AddressKeyFormat'])) {
      //					$a = $data['AddressValidationResponse'][0]['AddressKeyFormat'][0];
      //					if (isset($av_data['AddressClassificationCode'])) {
      //						$av_data['AddressClassificationCode']=$a['AddressClassification'][0]['Code'][0];
      //					} else {
      //						$av_data['AddressClassificationCode']="";
      //					}
      //					if (isset($av_data['ConsigneeName'])) {
      //						$av_data['ContactName']=$a['ConsigneeName'][0];
      //					} else {
      //						$av_data['ContactName']="";
      //					}
      //					if (isset($av_data['BuildingName'])) {
      //						$av_data['CompanyName']=$a['BuildingName'][0];
      //					} else {
      //						$av_data['CompanyName']="";
      //					}
      //					if (isset($av_data['AddressLine'])) {
      //						$av_data['AddressLine']=$a['AddressLine'][0];
      //					} else {
      //						$av_data['AddressLine']="";
      //					}
      //					if (isset($av_data['AddressLine'][1])) {
      //						$av_data['AddressLine2']=$a['AddressLine'][1];
      //					} else {
      //						$av_data['AddressLine2']="";
      //					}
      //					if (isset($av_data['PostcodePrimaryLow'])) {
      //						$av_data['PostalCodeLowEnd']=$a['PostcodePrimaryLow'][0];
      //					} else {
      //						$av_data['PostalCodeLowEnd']="";
      //					}
      //					if (isset($av_data['PostcodeExtendedLow'])) {
      //						$av_data['PostcodeExtendedLow']=$a['PostcodeExtendedLow'][0];
      //					} else {
      //						$av_data['PostcodeExtendedLow']="";
      //					}
      //					if (isset($av_data['PoliticalDivision2'])) {
      //						$av_data['City']=$a['PoliticalDivision2'][0];
      //					} else {
      //						$av_data['City']="";
      //					}
      //					if (isset($av_data['PoliticalDivision1'])) {
      //						$av_data['StateProvinceCode']=$a['PoliticalDivision1'][0];
      //					} else {
      //						$av_data['StateProvinceCode']="";
      //					}
      //
      //				}
      //				return($av_data);
      //			}
      //			else{
      return ($data);
      //        	}
    }
    else {
      return (FALSE);
    }

  }

  function getTimeInTransit($ups_pickupdate) {
    global $settings;
    $str = '<?xml version="1.0"?>' .
      '<AccessRequest xml:lang="en-US">' .
      '<AccessLicenseNumber>' . htmlspecialchars($this->accessKey, ENT_XML1) . '</AccessLicenseNumber>' .
      '<UserId>' . htmlspecialchars($this->user, ENT_XML1) . '</UserId>' .
      '<Password>' . htmlspecialchars($this->pass, ENT_XML1) . '</Password>' .
      '</AccessRequest>' .
      '<?xml version="1.0"?>' .
      '<TimeInTransitRequest xml:lang="en-US">' .
      '<Request>' .
      '<TransactionReference>' .
      '<CustomerContext>FYP TimeInTransit Request</CustomerContext>' .
      '<XpciVersion>1.0001</XpciVersion>' .
      '</TransactionReference>' .
      '<RequestAction>TimeInTransit</RequestAction>' .
      '</Request>' .
      '<TransitFrom>' .
      '<AddressArtifactFormat>' .
      '<PoliticalDivision2>' . $this->orig_city . '</PoliticalDivision2>' .
      '<PoliticalDivision1>' . $this->orig_state . '</PoliticalDivision1>' .
      '<CountryCode>' . $this->from_country . '</CountryCode>' .
      '<PostcodePrimaryLow>' . $this->orig_zip . '</PostcodePrimaryLow>' .
      '</AddressArtifactFormat>' .
      '</TransitFrom>' .
      '<TransitTo>' .
      '<AddressArtifactFormat>' .
      '<CountryCode>' . $this->to_country . '</CountryCode>' .
      '<PostcodePrimaryLow>' . htmlspecialchars($this->dest_zip, ENT_XML1) . '</PostcodePrimaryLow>' .
      '</AddressArtifactFormat>' .
      '</TransitTo>' .
      '<PickupDate>' . $ups_pickupdate . '</PickupDate>' .
      (($this->to_country == "US") ? '' : '<ShipmentWeight>' .
        '<UnitOfMeasurement>' .
        '<Code>' . htmlspecialchars($this->weightUnit, ENT_XML1) . '</Code>' .
        '</UnitOfMeasurement>' .
        '<Weight>1</Weight>' .
        '</ShipmentWeight>' .
        '<InvoiceLineTotal>' .
        '<CurrencyCode>USD</CurrencyCode>' .
        '<MonetaryValue>100.00</MonetaryValue>' .
        '</InvoiceLineTotal>') .
      '</TimeInTransitRequest>';
    //echo "<pre><b>UPS Request:</b><br>".htmlspecialchars($str)."</pre>".$this->av_server;

    $buffer = "";
    $c = curl_init();

    //		if($settings['ProxyAvailable'] == "YES"){
    //			curl_setopt($c, CURLOPT_VERBOSE, 1);
    //			if(defined("CURLOPT_PROXYTYPE") && defined("CURLPROXY_HTTP") && defined("CURLPROXY_SOCKS5")){
    //				curl_setopt($c, CURLOPT_PROXYTYPE, $settings['ProxyType'] == "HTTP" ? CURLPROXY_HTTP : CURLPROXY_SOCKS5);
    //			}
    //			curl_setopt($c, CURLOPT_PROXY, $settings['ProxyAddress'].":".$settings['ProxyPort']);
    //			if($settings['ProxyRequiresAuthorization'] == "YES"){
    //				curl_setopt($c, CURLOPT_PROXYUSERPWD, $settings['ProxyUsername'].":".$settings['ProxyPassword']);
    //			}
    //			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
    //			curl_setopt($c, CURLOPT_TIMEOUT, 120);
    //		}

    curl_setopt($c, CURLOPT_URL, $this->tnt_server);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_POSTFIELDS, $str);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    if (!ini_get('safe_mode')) {
      set_time_limit(3000);
    }

    for ($loop = 3; $loop > 0; $loop--) {
      $buffer = curl_exec($c);
      if ($buffer !== FALSE) {
        break;
      }
      error_log("UPS Get Time in Transit returned false! Retrying.");
      sleep(1);
    }

    //echo "<pre><b>UPS answer:</b><br>".htmlspecialchars($buffer)."</pre>";


    if ($buffer) {
      //			require_once("content/classes/class.xml.php");
      $xmlParser = new xmlToArrayParser();
      $data = $xmlParser->parse($buffer);
      //echo "<pre>";
      //print_r($data);
      //echo "</pre>";
      //exit;
      //die();
      if ($data['TimeInTransitResponse'][0]['Response'][0]['ResponseStatusCode'][0] == 1 and $data['TimeInTransitResponse'][0]['Response'][0]['ResponseStatusDescription'][0] == "Success") {
        $a = $data['TimeInTransitResponse'][0]['TransitResponse'][0];
        $a['pickup_date'] = date('m-d-Y', strtotime($a['PickupDate'][0]));
        return ($a);
      }
      else {
        error_log("UPS Get Time in Transit returned no data!" . print_r($data, TRUE));
        $errorLogFile = @fopen("ShipError.log", "a");
        fwrite($errorLogFile, sprintf("%s \n%s\n", date("D M j G:i:s T Y"), print_r($data, TRUE)));
        fclose($errorLogFile);
        return (FALSE);
      }
    }
    else {
      return (FALSE);
    }

  }

}
