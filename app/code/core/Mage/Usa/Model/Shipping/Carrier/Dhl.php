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
 * DHL shipping rates estimation
 *
 * @category   Mage
 * @package    Mage_Usa
 */
class Mage_Usa_Model_Shipping_Carrier_Dhl extends Mage_Usa_Model_Shipping_Carrier_Abstract
{
    protected $_request = null;
    protected $_result = null;
    protected $_errors = array();
    protected $_dhlRates = array();
    protected $_defaultGatewayUrl = 'https://eCommerce.airborne.com/ApiLandingTest.asp';

    const SUCCESS_CODE = 203;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::getStoreConfig('carriers/dhl/active')) {
            return false;
        }
        $this->process($request);
        return $this->getResult();
    }

    public function process(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        if ($request->getDhlId()) {
            $id = $request->getDhlId();
        } else {
            $id = Mage::getStoreConfig('carriers/dhl/id');
        }
        $r->setId($id);

        if ($request->getDhlPassword()) {
            $password = $request->getDhlPassword();
        } else {
            $password = Mage::getStoreConfig('carriers/dhl/password');
        }
        $r->setPassword($password);

        if ($request->getDhlAccount()) {
            $accountNbr = $request->getDhlAccount();
        } else {
            $accountNbr = Mage::getStoreConfig('carriers/dhl/account');
        }
        $r->setAccountNbr($accountNbr);

        if ($request->getDhlShippingKey()) {
            $shippingKey = $request->getDhlShippingKey();
        } else {
            $shippingKey = Mage::getStoreConfig('carriers/dhl/shipping_key');
        }
        $r->setShippingKey($shippingKey);

        if ($request->getDhlShippingIntlKey()) {
            $shippingKey = $request->getDhlShippingIntlKey();
        } else {
            $shippingKey = Mage::getStoreConfig('carriers/dhl/shipping_intlkey');
        }
        $r->setShippingIntlKey($shippingKey);

        if ($request->getDhlShipmentType()) {
            $shipmentType = $request->getDhlShipmentType();
        } else {
            $shipmentType = Mage::getStoreConfig('carriers/dhl/shipment_type');
        }
        $r->setShipmentType($shipmentType);

        if($request->getDhlDutiable()){
            $shipmentDutible = $request->getDhlDutiable();
        }else{
            $shipmentDutible = Mage::getStoreConfig('carriers/dhl/dutiable');
        }
        $r->setDutiable($shipmentDutible);

        if($request->getDhlDutyPaymentType()){
            $dutypaytype = $request->getDhlDutyPaymentType();
        }else{
          $dutypaytype = Mage::getStoreConfig('carriers/dhl/dutypaymenttype');
        }
        $r->setDutyPaymentType($dutypaytype);

        if($request->getDhlContentDesc()){
           $contentdesc = $request->getDhlContentDesc();
        }else{
          $contentdesc = Mage::getStoreConfig('carriers/dhl/contentdesc');
        }
        $r->setContentDesc($contentdesc);

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig('shipping/origin/country_id');
        }
        $r->setOrigCountry($origCountry);

        /*
        * DHL only accepts weight as a whole number. Maximum length is 3 digits.
        */
        $shipping_weight = $request->getPackageWeight();
        $shipping_weight = ($shipping_weight < .5 ? .5 : $shipping_weight);
        $shipping_weight = round($shipping_weight,0);


        $r->setWeight($shipping_weight)
            ->setValue(round($request->getPackageValue(),2))
            ->setDestStreet($request->getDestStreet())
            ->setDestCity($request->getDestCity())
            ->setDestCountryId($request->getDestCountryId())
            ->setDestState( Mage::getModel('usa/postcode')->getStateByPostcode($request->getDestPostcode()) );

        $this->_rawRequest = $r;
        $methods = explode(',', Mage::getStoreConfig('carriers/dhl/allowed_methods'));

        $internationcode=$this->getCode('international_searvice');

        foreach ($methods as $method) {
            if(($method==$internationcode && ($r->getDestCountryId() != self::USA_COUNTRY_ID)) ||
            ($method!=$internationcode && ($r->getDestCountryId() == self::USA_COUNTRY_ID)))
            {
        	   $this->_rawRequest->setService($method);
               $this->_getXmlQuotes();
            }
        }

        return $this;
    }

    public function getResult()
    {
        $result = Mage::getModel('shipping/rate_result');

        foreach ($this->_errors as $errorText) {
        	$error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('dhl');
            $error->setCarrierTitle(Mage::getStoreConfig('carriers/dhl/title'));
            $error->setErrorMessage($errorText);
            $result->append($error);
        }

        foreach($this->_dhlRates as $method => $data) {
            $rate = Mage::getModel('shipping/rate_result_method');
            $rate->setCarrier('dhl');
            $rate->setCarrierTitle(Mage::getStoreConfig('carriers/dhl/title'));
            $rate->setMethod($method);
            $rate->setMethodTitle($data['term']);
            $rate->setCost($data['price_total']);
            $rate->setPrice($data['price_total']);
            $result->append($rate);
        }

       return $result;
    }

    protected function _getXmlQuotes()
    {
        $r = $this->_rawRequest;

        $xml = new SimpleXMLElement('<eCommerce/>');
        $xml->addAttribute('action', 'Request');
        $xml->addAttribute('version', '1.1');

        $requestor = $xml->addChild('Requestor');
            $requestor->addChild('ID', $r->getId());
            $requestor->addChild('Password', $r->getPassword());

        if ($r->getDestCountryId() == self::USA_COUNTRY_ID) {
            $shipment = $xml->addChild('Shipment');
            $shipKey=$r->getShippingKey();
        }else{
             $shipment = $xml->addChild('IntlShipment');
             $shipKey=$r->getShippingIntlKey();

             /*
             * For internation shippingment customsvalue must be posted
             */
             $shippingDuty = $shipment->addChild('Dutiable');
                $shippingDuty->addChild('DutiableFlag',($r->getDutiable()?'Y':'N'));
                $shippingDuty->addChild('CustomsValue',$r->getValue());
        }

                $shipment->addAttribute('action', 'RateEstimate');
                $shipment->addAttribute('version', '1.0');

            $shippingCredentials = $shipment->addChild('ShippingCredentials');
                $shippingCredentials->addChild('ShippingKey',$shipKey);
                $shippingCredentials->addChild('AccountNbr', $r->getAccountNbr());

            $shipmentDetail = $shipment->addChild('ShipmentDetail');
                $shipmentDetail->addChild('ShipDate', date('Y-m-d'));
                $shipmentDetail->addChild('Service')->addChild('Code', $r->getService());
                $shipmentDetail->addChild('ShipmentType')->addChild('Code', $r->getShipmentType());
                $shipmentDetail->addChild('Weight', $r->getWeight());
                $shipmentDetail->addChild('ContentDesc', $r->getContentDesc());

             $billing = $shipment->addChild('Billing');
                $billing->addChild('Party')->addChild('Code', 'S');
                $billing->addChild('DutyPaymentType',$r->getDutyPaymentType());

            $receiverAddress = $shipment->addChild('Receiver')->addChild('Address');
                $receiverAddress->addChild('Street', ($r->getDestStreet()?$r->getDestStreet():'NA'));
                $receiverAddress->addChild('City', $r->getDestCity());
                $receiverAddress->addChild('State', $r->getDestState());
                /*
                * DHL xml service is using UK for united kingdom instead of GB which is a standard ISO country code
                */
                $receiverAddress->addChild('Country', ($r->getDestCountryId()=='GB'?'UK':$r->getDestCountryId()));
                $receiverAddress->addChild('PostalCode', $r->getDestPostal());
            /*
            $special_service=$this->getCode('special_service');
            if(array_key_exists($r->getService(),$special_service)){
                 $specialService = $shipment->addChild('SpecialServices')->addChild('SpecialService');
                 $specialService->addChild('Code',$special_service[$r->getService()]);
            }
            */


        $request = $xml->asXML();

        try {
            $url = Mage::getStoreConfig('carriers/dhl/gateway_url');
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
        $r = $this->_rawRequest;
        $costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve quotes';

        $tr = get_html_translation_table(HTML_ENTITIES);
        unset($tr['<'], $tr['>'], $tr['"']);
        $response = str_replace(array_keys($tr), array_values($tr), $response);


        if (strlen(trim($response))>0) {
            if (strpos(trim($response), '<?xml')===0) {
                $xml = simplexml_load_string($response);


                /*echo "<pre>DEBUG:\n";
                print_r($xml);
                echo "</pre>";*/


                if (is_object($xml)) {
                    $shipXml=(($r->getDestCountryId() == self::USA_COUNTRY_ID)?$xml->Shipment:$xml->IntlShipment);
                    if (
                        is_object($xml->Faults)
                        && is_object($xml->Faults->Fault)
                        && is_object($xml->Faults->Fault->Code)
                        && is_object($xml->Faults->Fault->Description)
                        && is_object($xml->Faults->Fault->Context)
                       ) {
                        $code = (string)$xml->Faults->Fault->Code;
                        $description = $xml->Faults->Fault->Description;
                        $context = $xml->Faults->Fault->Context;
                        $this->_errors[$code] = Mage::helper('usa')->__('Error #%s : %s (%s)', $code, $description, $context);
                    } elseif(
                        is_object($shipXml->Faults)
                        && is_object($shipXml->Faults->Fault)
                        && is_object($shipXml->Faults->Fault->Desc)
                        && intval($shipXml->Faults->Fault->Code) != self::SUCCESS_CODE
                       ) {
                           $code = (string)$shipXml->Faults->Fault->Code;
                           $description = $shipXml->Faults->Fault->Desc;
                           $this->_errors[$code] = Mage::helper('usa')->__('Error #%s: %s', $code, $description);
                    } elseif(
                        is_object($shipXml->Faults)
                        && is_object($shipXml->Result->Code)
                        && is_object($shipXml->Result->Desc)
                        && intval($shipXml->Result->Code) != self::SUCCESS_CODE
                       ) {
                           $code = (string)$shipXml->Result->Code;
                           $description = $shipXml->Result->Desc;
                           $this->_errors[$code] = Mage::helper('usa')->__('Error #%s: %s', $code, $description);
                    }else {
                        $this->_addRate($xml);
                        return $this;
                    }
                }
            } else {
                $this->_errors[] = Mage::helper('usa')->__('Response is in the wrong format');
            }
        }
    }

    public function getMethodPrice($cost, $method='')
    {
        $r = $this->_rawRequest;
        if (Mage::getStoreConfig('carriers/dhl/cutoff_cost') != ''
         && $method == Mage::getStoreConfig('carriers/dhl/free_method')
         && Mage::getStoreConfig('carriers/dhl/cutoff_cost') <= $r->getValue()) {
             $price = '0.00';
        } else {
            $price = $cost + Mage::getStoreConfig('carriers/dhl/handling');
        }
        return $price;
    }

    public function getCode($type, $code='')
    {
        static $codes;
        $codes = array(
            'service'=>array(
                'IE' => Mage::helper('usa')->__('International Express'),
                //'E SAT' => Mage::helper('usa')->__('Express Saturday'),
                //'E 10:30AM' => Mage::helper('usa')->__('Express 10:30 AM'),
                'E' => Mage::helper('usa')->__('Express'),
                'N' => Mage::helper('usa')->__('Next Afternoon'),
                'S' => Mage::helper('usa')->__('Second Day Service'),
                'G' => Mage::helper('usa')->__('Ground'),
            ),
            'shipment_type'=>array(
                'L' => Mage::helper('usa')->__('Letter'),
                'P' => Mage::helper('usa')->__('Package'),
            ),
            'international_searvice'=>'IE',
            /*
            'special_service'=>array(
                'E SAT'=>'SAT',
                'E 10:30AM'=>'1030',
            ),
            */
           'dutypayment_type'=>array(
                'S' => Mage::helper('usa')->__('Sender'),
                'R' => Mage::helper('usa')->__('Receiver'),
                '3' => Mage::helper('usa')->__('Third Party'),
           ),

        );



        if (!isset($codes[$type])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid DHL XML code type: %s', $type));
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid DHL XML code for type %s: %s', $type, $code));
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    protected function _addRate($xml)
    {
        $r = $this->_rawRequest;
        $services=$this->getCode('service');
        $shipXml=(($r->getDestCountryId() == self::USA_COUNTRY_ID)?$xml->Shipment:$xml->IntlShipment);
        $desc=(string)$shipXml->EstimateDetail->ServiceLevelCommitment->Desc;
        $totalEstimate=(string)$shipXml->EstimateDetail->RateEstimate->TotalChargeEstimate;
        /*
        * DHL can return with empty result and success code
        * we need to make sure there is shipping estimate and code
        */
        if($desc && $totalEstimate){
            $service = (string)$shipXml->EstimateDetail->Service->Code;
            $data['term'] = (isset($services[$service])?$services[$service]:$desc);
            $data['price_total'] = $totalEstimate;
            $this->_dhlRates[$service] = $data;
        }
    }

    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings=array($trackings);
        }
       $this->_getXMLTracking($trackings);

       return $this->_result;
    }

    protected function setTrackingReqeust()
    {
        $r = new Varien_Object();

        $id = Mage::getStoreConfig('carriers/dhl/id');
        $r->setId($id);

        $password = Mage::getStoreConfig('carriers/dhl/password');
        $r->setPassword($password);

        $this->_rawTrackRequest = $r;
    }

    protected function _getXMLTracking($trackings)
    {
        $r = $this->_rawTrackRequest;

        $xml = new SimpleXMLElement('<eCommerce/>');
        $xml->addAttribute('action', 'Request');
        $xml->addAttribute('version', '1.1');

        $requestor = $xml->addChild('Requestor');
            $requestor->addChild('ID', $r->getId());
            $requestor->addChild('Password', $r->getPassword());

        $track=$xml->addChild('Track');
            $track->addAttribute('action', 'Get');
            $track->addAttribute('version', '1.0');

            //shippment has not been delivered or no scans
            //$track->addChild('Shipment')->addChild('TrackingNbr','1231230011');
            //home shipment
            //$track->addChild('Shipment')->addChild('TrackingNbr','2342340011');
            //international shipment
            //$track->addChild('Shipment')->addChild('TrackingNbr','5675670011');
            //tracking not in airborme tracking tsystem
            //$track->addChild('Shipment')->addChild('TrackingNbr','7897890011');
            //tracking need to contanct customer service for more information
            //$track->addChild('Shipment')->addChild('TrackingNbr','8198190011');

            foreach($trackings as $tracking){
               $track->addChild('Shipment')->addChild('TrackingNbr',$tracking);
            }

         $request = $xml->asXML();

         /*
         * tracking api cannot process from 3pm to 5pm PST time on Sunday
         * DHL Airborne conduts a maintainance during that period.
         */
         try {
            $url = Mage::getStoreConfig('carriers/dhl/gateway_url');
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
        $this->_parseXmlTrackingResponse($trackings, $responseBody);
    }

    protected function _parseXmlTrackingResponse($trackings, $response)
    {
         $errorTitle = 'Unable to retrieve tracking';
         $resultArr=array();
         $errorArr=array();
         $trackingserror=array();
         $tracknum='';
          if (strlen(trim($response))>0) {
            if (strpos(trim($response), '<?xml')===0) {
                 $xml = simplexml_load_string($response);
                 if (is_object($xml)) {
                     $trackxml=$xml->Track;
                      if (
                        is_object($xml->Faults)
                        && is_object($xml->Faults->Fault)
                        && is_object($xml->Faults->Fault->Code)
                        && is_object($xml->Faults->Fault->Description)
                        && is_object($xml->Faults->Fault->Context)
                       ) {
                        $code = (string)$xml->Faults->Fault->Code;
                        $description = $xml->Faults->Fault->Description;
                        $context = $xml->Faults->Fault->Context;
                        $errorTitle = Mage::helper('usa')->__('Error #%s : %s (%s)', $code, $description, $context);
                    }elseif(is_object($trackxml) && is_object($trackxml->Shipment)){
                        foreach($trackxml->Shipment as $txml){
                         $rArr=array();

                            if(is_object($txml)){
                                $tracknum=(string)$txml->TrackingNbr;
                                if($txml->Fault){
                                     $code = (string)$txml->Fault->Code;
                                     $description   = $txml->Fault->Description;
                                     $errorArr[$tracknum] = Mage::helper('usa')->__('Error #%s: %s', $code, $description);
                                }elseif($txml->Result){
                                    $code = (int)$txml->Result->Code;
                                    if($code===0){
                                         /*
                                        * Code 0== airbill  found
                                        */
                                        $rArr['service']=(string)$txml->Service->Desc;
                                        if($txml->Delivery){
                                            $rArr['deliverydate']=(string)$txml->Delivery->Date;
                                            $rArr['deliverytime']=(string)$txml->Delivery->Time.':00';
                                            $rArr['status'] = Mage::helper('usa')->__('Delivered');
                                        }else{
                                             $rArr['status']=(string)$txml->ShipmentType->Desc.Mage::helper('usa')->__(' was not delivered nor scanned');
                                        }
                                        $resultArr[$tracknum]=$rArr;
                                    }else{
                                        $description =(string)$txml->Result->Desc;
                                        if($description)
                                            $errorArr[$tracknum]=Mage::helper('usa')->__('Error #%s: %s', $code, $description);
                                        else
                                            $errorArr[$tracknum]=Mage::helper('usa')->__('Unable to retrieve tracking');
                                    }
                                }else{
                                    $errorArr[$tracknum]=Mage::helper('usa')->__('Unable to retrieve tracking');
                                }

                            }
                        }

                    }
                 }
            } else {
                $errorTitle = Mage::helper('usa')->__('Response is in the wrong format');
            }
          }

          $result = Mage::getModel('shipping/tracking_result');
          if($errorArr || $resultArr){
            foreach ($errorArr as $t=>$r) {
            	$error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier('dhl');
                $error->setCarrierTitle(Mage::getStoreConfig('carriers/dhl/title'));
                $error->setTracking($t);
                $error->setErrorMessage($r);
                $result->append($error);
            }

            foreach($resultArr as $t => $data) {
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier('dhl');
                $tracking->setCarrierTitle(Mage::getStoreConfig('carriers/dhl/title'));
                $tracking->setTracking($t);
                $tracking->addData($data);
                /*
                $tracking->setStatus($data['status']);
                $tracking->setService($data['service']);
                if(isset($data['deliverydate'])) $tracking->setDeliveryDate($data['deliverydate']);
                if(isset($data['deliverytime'])) $tracking->setDeliveryTime($data['deliverytime']);
                */
                $result->append($tracking);
            }
          }else{
              foreach($trackings as $t){
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier('dhl');
                $error->setCarrierTitle(Mage::getStoreConfig('carriers/dhl/title'));
                $error->setTracking($t);
                $error->setErrorMessage($errorTitle);
                $result->append($error);

              }
          }
        $this->_result = $result;
//echo "<pre>";print_r($result);

    }

    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking){
                    if($data = $tracking->getAllData()){
                        if (isset($data['status'])) {
                            $statuses .= Mage::helper('usa')->__($data['status'])."\n<br>";
                        } else {
                            $statuses .= Mage::helper('usa')->__($data['error_message'])."\n<br>";
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
