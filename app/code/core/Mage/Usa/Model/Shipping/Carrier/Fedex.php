<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Usa
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fedex shipping rates estimation
 *
 * @category   Mage
 * @package    Mage_Usa
 */
class Mage_Usa_Model_Shipping_Carrier_Fedex extends Mage_Usa_Model_Shipping_Carrier_Abstract
{
    protected $_request = null;
    protected $_result = null;
    protected $_gatewayUrl = 'https://gateway.fedex.com/GatewayDC';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::getStoreConfig('carriers/fedex/active')) {
            return false;
        }

        $this->setRequest($request);

        $this->_getXmlQuotes();

        return $this->getResult();
    }

    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        if ($request->getFedexAccount()) {
            $account = $request->getFedexAccount();
        } else {
            $account = Mage::getStoreConfig('carriers/fedex/account');
        }
        $r->setAccount($account);

        if ($request->getFedexDropoff()) {
            $dropoff = $request->getFedexDropoff();
        } else {
            $dropoff = Mage::getStoreConfig('carriers/fedex/dropoff');
        }
        $r->setDropoffType($dropoff);

        if ($request->getFedexPackaging()) {
            $packaging = $request->getFedexPackaging();
        } else {
            $packaging = Mage::getStoreConfig('carriers/fedex/packaging');
        }
        $r->setPackaging($packaging);

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig('shipping/origin/country_id');
        }
        $r->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code());

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode'));
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        } else {

        }

        $r->setWeight($request->getPackageWeight());

        $r->setValue($request->getPackageValue());

        $this->_rawRequest = $r;

        return $this;
    }

    public function getResult()
    {
       return $this->_result;
    }

    protected function _getXmlQuotes()
    {
        $r = $this->_rawRequest;

        $xml = new SimpleXMLElement('<FDXRateAvailableServicesRequest/>');

        $xml->addAttribute('xmlns:api', 'http://www.fedex.com/fsmapi');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'FDXRateAvailableServicesRequest.xsd');

        $requestHeader = $xml->addChild('RequestHeader');
//          $requestHeader->addChild('CustomerTransactionIdentifier', 'CTIString');
            $requestHeader->addChild('AccountNumber', $r->getAccount());
//          $requestHeader->addChild('MeterNumber', '2436351');  -- my own meter number
            $requestHeader->addChild('MeterNumber', '0');
//          $requestHeader->addChild('CarrierCode', 'FDXE');
//          $requestHeader->addChild('CarrierCode', 'FDXG');
            /**
             *  FDXE � FedEx Express
             *  FDXG � FedEx Ground
             */

        $xml->addChild('ShipDate', date('Y-m-d'));
//      $xml->addChild('ReturnShipmentIndicator', 'NONRETURN');
        /**
         *  � NONRETURN
         *  � PRINTRETURNLABEL
         *  � EMAILLABEL
         */
        $xml->addChild('DropoffType', $r->getDropoffType());
        /**
         *  � REGULARPICKUP
         *  � REQUESTCOURIER
         *  � DROPBOX
         *  � BUSINESSSERVICECENTER
         *  � STATION
         *  Only REGULARPICKUP, REQUESTCOURIER, and STATION are
         *  allowed with international freight shipping.
         */
        if ($r->hasService()) {
            $xml->addChild('Service', $r->getService());
        }
        /**
         *  One of the following FedEx Services is optional:
         *  � PRIORITYOVERNIGHT
         *  � STANDARDOVERNIGHT
         *  � FIRSTOVERNIGHT
         *  � FEDEX2DAY
         *  � FEDEXEXPRESSSAVER
         *  � INTERNATIONALPRIORITY
         *  � INTERNATIONALECONOMY
         *  � INTERNATIONALFIRST
         *  � FEDEX1DAYFREIGHT
         *  � FEDEX2DAYFREIGHT
         *  � FEDEX3DAYFREIGHT
         *  � FEDEXGROUND
         *  � GROUNDHOMEDELIVERY
         *  � INTERNATIONALPRIORITY FREIGHT
         *  � INTERNATIONALECONOMY FREIGHT
         *  � EUROPEFIRSTINTERNATIONALPRIORITY
         *  If provided, only that service�s estimated charges will be returned.
         */
        $xml->addChild('Packaging', $r->getPackaging());
        /**
         *  One of the following package types is required:
         *  � FEDEXENVELOPE
         *  � FEDEXPAK
         *  � FEDEXBOX
         *  � FEDEXTUBE
         *  � FEDEX10KGBOX
         *  � FEDEX25KGBOX
         *  � YOURPACKAGING
         *  If value entered is FEDEXENVELOPE, FEDEX10KGBOX, or
         *  FEDEX25KGBOX, an MPS rate quote is not allowed.
         */
        $xml->addChild('WeightUnits', 'LBS');
        /**
         *  � LBS
         *  � KGS
         *  LBS is required for a U.S. FedEx Express rate quote.
         */
        $xml->addChild('Weight', $r->getWeight());
//      $xml->addChild('ListRate', 'true');
        /**
         *  Optional.
         *  If = true or 1, list-rate courtesy quotes should be returned in addition to
         *  the discounted quote.
         */

        $originAddress = $xml->addChild('OriginAddress');
//          $originAddress->addChild('StateOrProvinceCode', 'GA');   -- ???
            $originAddress->addChild('PostalCode', $r->getOrigPostal());
            $originAddress->addChild('CountryCode', $r->getOrigCountry());

        $destinationAddress = $xml->addChild('DestinationAddress');
//          $destinationAddress->addChild('StateOrProvinceCode', 'GA');   -- ???
            $destinationAddress->addChild('PostalCode', $r->getDestPostal());
            $destinationAddress->addChild('CountryCode', $r->getDestCountry());

        $payment = $xml->addChild('Payment');
            $payment->addChild('PayorType', 'SENDER');
            /**
             *  Optional.
             *  Defaults to SENDER.
             *  If value other than SENDER is used, no rates will still be returned.
             */

        /**
         *  DIMENSIONS
         *
         *  Dimensions / Length
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The length of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Width
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The width of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Height
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The height of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Units
         *  Required if dimensions are entered.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The valid unit of measure codes for the package dimensions are:
         *  IN � Inches
         *  CM � Centimeters
         *  U.S. FedEx Express must be in inches.
         */

        $declaredValue = $xml->addChild('DeclaredValue');
            $declaredValue->addChild('Value', $r->getValue());
            $declaredValue->addChild('CurrencyCode', 'USD');

//      $specialServices = $xml->addChild('SpecialServices');
//          $specialServices->addChild('Alcohol', 'true');
//          $specialServices->addChild('DangerousGoods', 'true')->addChild('Accessibility', 'ACCESSIBLE');
        /**
         *  Valid values:
         *  ACCESSIBLE � accessible DG
         *  INACCESSIBLE � inaccessible DG
         */
//          $specialServices->addChild('DryIce', 'true');
//          $specialServices->addChild('ResidentialDelivery', 'true');
        /**
         *  If = true or 1, the shipment is Residential Delivery. If Recipient Address
         *  is in a rural area (defined by table lookup), additional charge will be
         *  applied. This element is not applicable to the FedEx Home Delivery
         *  service.
         */
//          $specialServices->addChild('InsidePickup', 'true');
//          $specialServices->addChild('InsideDelivery', 'true');
//          $specialServices->addChild('SaturdayPickup', 'true');
//          $specialServices->addChild('SaturdayDelivery', 'true');
//          $specialServices->addChild('NonstandardContainer', 'true');
//          $specialServices->addChild('SignatureOption', 'true');
        /**
         *  Optional.
         *  Specifies the Delivery Signature Option requested for the shipment.
         *  Valid values:
         *  � DELIVERWITHOUTSIGNATURE
         *  � INDIRECT
         *  � DIRECT
         *  � ADULT
         *  For FedEx Express shipments, the DELIVERWITHOUTSIGNATURE
         *  option will not be allowed when the following special services are
         *  requested:
         *  � Alcohol
         *  � Hold at Location
         *  � Dangerous Goods
         *  � Declared Value greater than $500
         */

        /**
         *  HOMEDELIVERY
         *
         *  HomeDelivery / Type
         *  One of the following values are required for FedEx Home Delivery
         *  shipments:
         *  � DATECERTAIN
         *  � EVENING
         *  � APPOINTMENT
         *
         *  PackageCount
         *  Required for multiple-piece shipments (MPS).
         *  For MPS shipments, 1 piece = 1 box.
         *  For international Freight MPS shipments, this is the total number of
         *  "units." Units are the skids, pallets, or boxes that make up a freight
         *  shipment.
         *  Each unit within a shipment should have its own label.
         *  FDXE only applies to COD, MPS, and international.
         *  Valid values: 1 to 999
         */

        /**
         *  VARIABLEHANDLINGCHARGES
         *
         *  VariableHandlingCharges / Level
         *  Optional.
         *  Only applicable if valid Variable Handling Type is present.
         *  Apply fixed or variable handling charges at package or shipment level.
         *  Valid values:
         *  � PACKAGE
         *  � SHIPMENT
         *  The value "SHIPMENT" is applicable only on last piece of FedEx
         *  Ground or FedEx Express MPS shipment only.
         *  Note: Value "SHIPMENT" = shipment level affects the entire shipment.
         *  Anything else sent in Child will be ignored.
         *
         *  VariableHandlingCharges / Type
         *  Optional.
         *  If valid value is present, a valid Variable Handling Charge is required.
         *  Specifies what type of Variable Handling charges to assess and on
         *  which amount.
         *  Valid values:
         *  � FIXED_AMOUNT
         *  � PERCENTAGE_OF_BASE
         *  � PERCENTAGE_OF_NET
         *  � PERCENTAGE_OF_NET_ EXCL_TAXES
         *
         *  VariableHandlingCharges / AmountOrPercentage
         *  Optional.
         *  Required in conjunction with Variable Handling Type.
         *  Contains the dollar or percentage amount to be added to the Freight
         *  charges. Whether the amount is a dollar or percentage is based on the
         *  Variable Handling Type value that is included in this Request.
         *  Format: Two explicit decimal positions (e.g. 1.00); 10 total length
         *  including decimal place.
         */

        $xml->addChild('PackageCount', '1');

        $request = $xml->asXML();

/*
        $client = new Zend_Http_Client();
        $client->setUri(Mage::getStoreConfig('carriers/fedex/gateway_url'));
        $client->setConfig(array('maxredirects'=>0, 'timeout'=>30));
        $client->setParameterPost($request);
        $response = $client->request();
        $responseBody = $response->getBody();
*/

        try {
            $url = Mage::getStoreConfig('carriers/fedex/gateway_url');
            if (!$url) {
                $url = $this->_defaultGatewayUrl;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $responseBody = curl_exec($ch);
            curl_close ($ch);
        } catch (Exception $e) {
            $responseBody = '';
        }


        $this->_parseXmlResponse($responseBody);
    }

    protected function _parseXmlResponse($response)
    {
    	$costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve quotes';
        if (strlen(trim($response))>0) {
            if (strpos(trim($response), '<?xml')===0) {
                $xml = simplexml_load_string($response);
                if (is_object($xml)) {
                    if (is_object($xml->Error) && is_object($xml->Error->Message)) {
                        $errorTitle = (string)$xml->Error->Message;
                    } elseif (is_object($xml->SoftError) && is_object($xml->SoftError->Message)) {
                    	$errorTitle = (string)$xml->SoftError->Message;
                    } else {
                        $errorTitle = 'Unknown error';
                    }
                    $allowedMethods = explode(",", Mage::getStoreConfig('carriers/fedex/allowed_methods'));
                    foreach ($xml->Entry as $entry) {
                        if (in_array((string)$entry->Service, $allowedMethods)) {
                            $costArr[(string)$entry->Service] = (string)$entry->EstimatedCharges->DiscountedCharges->NetCharge;
                            $priceArr[(string)$entry->Service] = $this->getMethodPrice((string)$entry->EstimatedCharges->DiscountedCharges->NetCharge, (string)$entry->Service);
                        }
                    }
                    asort($priceArr);
                }
            } else {
                $errorTitle = 'Response is in the wrong format';
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle(Mage::getStoreConfig('carriers/fedex/title'));
            $error->setErrorMessage($errorTitle);
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('fedex');
                $rate->setCarrierTitle(Mage::getStoreConfig('carriers/fedex/title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($this->getCode('method', $method));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
        $this->_result = $result;
    }

    public function getMethodPrice($cost, $method='')
    {
        $r = $this->_rawRequest;
        if (Mage::getStoreConfig('carriers/fedex/cutoff_cost') != ''
         && $method == Mage::getStoreConfig('carriers/fedex/free_method')
         && Mage::getStoreConfig('carriers/fedex/cutoff_cost') <= $r->getValue()) {
             $price = '0.00';
        } else {
            $price = $cost + Mage::getStoreConfig('carriers/fedex/handling');
        }
        return $price;
    }

/*
    public function isEligibleForFree($method)
    {
    	return $method=='FEDEXGROUND';
    }
*/

    public function getCode($type, $code='')
    {
        $codes = array(

            'method'=>array(
                'PRIORITYOVERNIGHT'                => Mage::helper('usa')->__('Priority Overnight'),
                'STANDARDOVERNIGHT'                => Mage::helper('usa')->__('Standard Overnight'),
                'FIRSTOVERNIGHT'                   => Mage::helper('usa')->__('First Overnight'),
                'FEDEX2DAY'                        => Mage::helper('usa')->__('2Day'),
                'FEDEXEXPRESSSAVER'                => Mage::helper('usa')->__('Express Saver'),
                'INTERNATIONALPRIORITY'            => Mage::helper('usa')->__('International Priority'),
                'INTERNATIONALECONOMY'             => Mage::helper('usa')->__('International Economy'),
                'INTERNATIONALFIRST'               => Mage::helper('usa')->__('International First'),
                'FEDEX1DAYFREIGHT'                 => Mage::helper('usa')->__('1 Day Freight'),
                'FEDEX2DAYFREIGHT'                 => Mage::helper('usa')->__('2 Day Freight'),
                'FEDEX3DAYFREIGHT'                 => Mage::helper('usa')->__('3 Day Freight'),
                'FEDEXGROUND'                      => Mage::helper('usa')->__('Ground'),
                'GROUNDHOMEDELIVERY'               => Mage::helper('usa')->__('Home Delivery'),
                'INTERNATIONALPRIORITY FREIGHT'    => Mage::helper('usa')->__('Intl Priority Freight'),
                'INTERNATIONALECONOMY FREIGHT'     => Mage::helper('usa')->__('Intl Economy Freight'),
                'EUROPEFIRSTINTERNATIONALPRIORITY' => Mage::helper('usa')->__('Europe First Priority'),
            ),

            'dropoff'=>array(
                'REGULARPICKUP'         => Mage::helper('usa')->__('Regular Pickup'),
                'REQUESTCOURIER'        => Mage::helper('usa')->__('Request Courier'),
                'DROPBOX'               => Mage::helper('usa')->__('Drop Box'),
                'BUSINESSSERVICECENTER' => Mage::helper('usa')->__('Business Service Center'),
                'STATION'               => Mage::helper('usa')->__('Station'),
            ),

            'packaging'=>array(
                'FEDEXENVELOPE' => Mage::helper('usa')->__('FedEx Envelope'),
                'FEDEXPAK'      => Mage::helper('usa')->__('FedEx Pak'),
                'FEDEXBOX'      => Mage::helper('usa')->__('FedEx Box'),
                'FEDEXTUBE'     => Mage::helper('usa')->__('FedEx Tube'),
                'FEDEX10KGBOX'  => Mage::helper('usa')->__('FedEx 10kg Box'),
                'FEDEX25KGBOX'  => Mage::helper('usa')->__('FedEx 25kg Box'),
                'YOURPACKAGING' => Mage::helper('usa')->__('Your Packaging'),
            ),

        );

        if (!isset($codes[$type])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid FedEx XML code type: %s', $type));
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid FedEx XML code for type %s: %s', $type, $code));
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings=array($trackings);
        }

        foreach($trackings as $tracking){
            $this->_getXMLTracking($tracking);
        }

        return $this->_result;
    }

    protected function setTrackingReqeust()
    {
        $r = new Varien_Object();

        $account = Mage::getStoreConfig('carriers/fedex/account');
        $r->setAccount($account);

        $this->_rawTrackingRequest = $r;

    }
    protected function _getXMLTracking($tracking)
    {
        $r = $this->_rawTrackingRequest;

        $xml = new SimpleXMLElement('<FDXTrack2Request/>');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'FDXTrack2Request.xsd');

        $requestHeader = $xml->addChild('RequestHeader');
        $requestHeader->addChild('AccountNumber', $r->getAccount());
        /*
        * for tracking result, actual meter number is not needed
        */
        $requestHeader->addChild('MeterNumber', '0');

        $packageIdentifier = $xml->addChild('PackageIdentifier');
        $packageIdentifier->addChild('Value', $tracking);

        $request = $xml->asXML();

        try {
            $url = Mage::getStoreConfig('carriers/fedex/gateway_url');
            if (!$url) {
                $url = $this->_defaultGatewayUrl;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $responseBody = curl_exec($ch);
            curl_close ($ch);
        } catch (Exception $e) {
            $responseBody = '';
        }

        #echo "<xmp>".$responseBody."</xmp>";
		$this->_parseXmlTrackingResponse($tracking, $responseBody);
    }

    protected function _parseXmlTrackingResponse($trackingvalue,$response)
    {
         $errorTitle = 'Unable to retrieve tracking';
         $resultArr=array();
         if (strlen(trim($response))>0) {
              if (strpos(trim($response), '<?xml')===0) {
                  $xml = simplexml_load_string($response);
                  if (is_object($xml)) {
                    if (is_object($xml->Error) && is_object($xml->Error->Message)) {
                        $errorTitle = (string)$xml->Error->Message;
                    } elseif (is_object($xml->SoftError) && is_object($xml->SoftError->Message)) {
                    	$errorTitle = (string)$xml->SoftError->Message;
                    } else {
                        $errorTitle = 'Unknown error';
                    }
                  }else{
                      $errorTitle = 'Error in loading response';
                  }

                  $resultArr['status']=(string)$xml->Package->StatusDescription;
                  $resultArr['service']=(string)$xml->Package->Service;
                  $resultArr['deliverydate']=(string)$xml->Package->DeliveredDate;
                  $resultArr['deliverytime']=(string)$xml->Package->DeliveredTime;
                  $resultArr['deliverylocation']=(string)$xml->TrackProfile->DeliveredLocationDescription;
                  $resultArr['signedby']=(string)$xml->Package->SignedForBy;
              }else {
                $errorTitle = 'Response is in the wrong format';
              }
         }

         if(!$this->_result){
             $this->_result = Mage::getModel('shipping/tracking_result');
         }
         $defaults = $this->getDefaults();

         if($resultArr){
             $tracking = Mage::getModel('shipping/tracking_result_status');
             $tracking->setCarrier('fedex');
             $tracking->setCarrierTitle(Mage::getStoreConfig('carriers/fedex/title'));
             $tracking->setTracking($trackingvalue);
             $tracking->addData($resultArr);
             /*$tracking->setStatus($resultArr['status']);
             $tracking->setService($resultArr['service']);
             $tracking->setDeliveryDate($resultArr['deliverydate']);
             $tracking->setDeliveryTime($resultArr['deliverytime']);
             $tracking->setDeliveryLocation($resultArr['deliverylocation']);
             if(isset($resultArr['signedby']))$tracking->setSignedBy($resultArr['signedby']);
             */
             $this->_result->append($tracking);
         }else{
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle(Mage::getStoreConfig('carriers/fedex/title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $this->_result->append($error);
         }
    }

    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking){
                    if($data = $tracking->getAllData()){
                        if (!empty($data['status'])) {
                            $statuses .= Mage::helper('usa')->__($data['status'])."\n<br>";
                        } else {
                            $statuses .= Mage::helper('usa')->__('Empty response')."\n<br>";
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('usa')->__('Empty response');
        }
        return $statuses;
    }

    public function isTrackingAvailable()
    {
        return true;
    }

}
