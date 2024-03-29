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
 * @package    Mage_Paypal
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Express Checkout Module
 *
 */
class Mage_Paypal_Model_Express extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'paypal_express';
    protected $_formBlockType = 'paypal/express_form';
    protected $_infoBlockType = 'paypal/express_info';

    /**
     * Availability options
     */
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    /**
     * Get Paypal API Model
     *
     * @return Mage_Paypal_Model_Api_Nvp
     */
    public function getApi()
    {
        return Mage::getSingleton('paypal/api_nvp');
    }

    /**
     * Get paypal session namespace
     *
     * @return Mage_Paypal_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('paypal/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function getRedirectUrl()
    {
        return $this->getApi()->getRedirectUrl();
    }

    public function getCountryRegionId()
    {
        $a = $this->getApi()->getShippingAddress();
        return $this;
    }

    /**
     * Using internal pages for input payment data
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return false;
    }

    public function catchError()
    {
        if ($this->getApi()->hasError()) {
            $s = Mage::getSingleton('checkout/session');
            $e = $this->getApi()->getError();
            switch ($e['type']) {
                case 'CURL':
                    $s->addError(Mage::helper('paypal')->__('There was an error connecting to the Paypal server: %s', $e['message']));
                    break;

                case 'API':
                    $s->addError(Mage::helper('paypal')->__('There was an error during communication with Paypal: %s - %s', $e['short_message'], $e['long_message']));
                    break;
            }
        }
        return $this;
    }

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('paypal/express_form', $name)
            ->setMethod('paypal_express')
            ->setPayment($this->getPayment())
            ->setTemplate('paypal/express/form.phtml');

        return $block;
    }

    public function createInfoBlock($name)
    {
        $block = $this->getLayout()->createBlock('paypal/express_info', $name)
            ->setPayment($this->getPayment())
            ->setTemplate('paypal/express/info.phtml');
        return $block;
    }

    public function getCheckoutRedirectUrl()
    {
        return Mage::getUrl('paypal/express/mark');
    }

    public function getPaymentAction()
    {
        $paymentAction = Mage::getStoreConfig('payment/paypal_express/payment_action');
        if (!$paymentAction) {
            $paymentAction = Mage_Paypal_Model_Api_Nvp::PAYMENT_TYPE_AUTH;
        }
        return $paymentAction;
    }

    public function shortcutSetExpressCheckout()
    {
        $this->getApi()
            ->setPaymentType($this->getPaymentAction())
            ->setAmount($this->getQuote()->getGrandTotal())
            ->setCurrencyCode($this->getQuote()->getStoreCurrencyCode())
            ->callSetExpressCheckout();

        $this->catchError();

        return $this;
    }

    public function markSetExpressCheckout()
    {
        $address = $this->getQuote()->getShippingAddress();
        $this->getApi()
            ->setPaymentType($this->getPaymentAction())
            ->setAmount($address->getGrandTotal())
            ->setCurrencyCode($this->getQuote()->getStoreCurrencyCode())
            ->setShippingAddress($address)
            ->callSetExpressCheckout();

        $this->catchError();

        return $this;
    }

    public function returnFromPaypal()
    {
        $this->_getExpressCheckoutDetails();
        switch ($this->getApi()->getUserAction()) {
            case Mage_Paypal_Model_Api_Nvp::USER_ACTION_CONTINUE:
                $this->getApi()->setRedirectUrl(Mage::getUrl('paypal/express/review'));
                break;

            case Mage_Paypal_Model_Api_Nvp::USER_ACTION_COMMIT:
                $this->getApi()->setRedirectUrl(Mage::getUrl('paypal/express/saveOrder'));
                break;
        }
        return $this;
    }

    protected function _getExpressCheckoutDetails()
    {
        $api = $this->getApi();
        if (!$api->callGetExpressCheckoutDetails()) {
            Mage::throwException(Mage::helper('paypal')->__('Problem during communication with PayPal'));
        }
        $q = $this->getQuote();
        $a = $api->getShippingAddress();

        $a->setCountryId(
            Mage::getModel('directory/country')->loadByCode($a->getCountry())->getId()
        );
        $a->setRegionId(
            Mage::getModel('directory/region')->loadByCode($a->getRegion(), $a->getCountryId())->getId()
        );

        $q->getBillingAddress()
            ->setFirstname($a->getFirstname())
            ->setLastname($a->getLastname())
            ->setEmail($a->getEmail());

        $q->getShippingAddress()
            ->importCustomerAddress($a)
            ->setCollectShippingRates(true);

        $q->setCheckoutMethod('paypal_express');

        $q->getPayment()
            ->setMethod('paypal_express')
            ->setPaypalCorrelationId($api->getCorrelationId())
            ->setPaypalPayerId($api->getPayerId())
            ->setPaypalPayerStatus($api->getPayerStatus())
        ;

        $q->collectTotals()->save();

    }


    public function capture(Varien_Object $payment, $amount)
    {
        $api = $this->getApi()
            ->setPaymentType(Mage_Paypal_Model_Api_Nvp::PAYMENT_TYPE_SALE)
            ->setAmount($amount)
            ->setBillingAddress($payment->getOrder()->getBillingAddress())
            ->setPayment($payment);
        ;

        $api->setAuthorizationId($payment->getCcTransId())
            ->setCompleteType('NotComplete');
        $result = $api->callDoCapture()!==false;

        if ($result) {
            $payment
                ->setStatus('APPROVED')
                ->setCcTransId($api->getTransactionId());
        } else {
            $e = $api->getError();
            if (isset($e['short_message'])) {
                $message = $e['short_message'];
            } else {
                $message = Mage::helper('paypal')->__("Unknown PayPal API error: %s", $e['code']);
            }
            if (isset($e['long_message'])) {
                $message .= ': '.$e['long_message'];
            }
            Mage::throwException($message);
        }
        return $this;
    }

    public function placeOrder(Varien_Object $payment)
    {
        $api = $this->getApi();
        $api->setAmount($payment->getQuote()->getGrandTotal())
            ->setCurrencyCode($payment->getQuote()->getQuoteCurrencyCode());

        if ($api->callDoExpressCheckoutPayment()!==false) {
            $payment->setStatus('APPROVED')
                ->setCcTransId($api->getTransactionId())
                ->setPayerId($api->getPayerId());
        } else {
            $e = $api->getError();
            Mage::throwException($e['short_message'].': '.$e['long_message']);
        }
        return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }

      /**
      * void
      *
      * @access public
      * @param string $payment Varien_Object object
      * @return Mage_Payment_Model_Abstract
      */
    public function void(Varien_Object $payment)
    {
        if($payment->getCcTransId()){
            $api = $this->getApi();
            $api->setAuthorizationId($payment->getCcTransId());

             if ($api->callDoVoid()!==false){
                 $payment->setStatus('SUCCESS')
                    ->setCcTransId($api->getTransactionId());
             }else{
               $e = $api->getError();
               $payment->setStatus('ERROR')
                    ->setStatusDescription($e['short_message'].': '.$e['long_message']);
             }
        }else{
            $payment->setStatus('ERROR');
            $payment->setStatusDescription(Mage::helper('paypal')->__('Invalid transaction id'));
        }
        return $this;
    }

      /**
      * refund the amount with transaction id
      *
      * @access public
      * @param string $payment Varien_Object object
      * @return Mage_Payment_Model_Abstract
      */
      public function refund(Varien_Object $payment, $amount)
      {
          if($payment->getCcTransId() && $payment->getAmount()>0){
              $api = $this->getApi();
              //we can refund the amount full or partial so it is good to set up as partial refund
              $api->setTransactionId($payment->getCcTransId())
                ->setRefundType(Mage_Paypal_Model_Api_Nvp::REFUND_TYPE_PARTIAL)
                ->setAmount($amount);

             if ($api->callRefundTransaction()!==false){
                 $payment->setStatus('SUCCESS')
                    ->setCcTransId($api->getTransactionId());
             }else{
               $e = $api->getError();
               $payment->setStatus('ERROR')
                    ->setStatusDescription($e['short_message'].': '.$e['long_message']);
             }


          }else{
            $payment->setStatus('ERROR');
            $payment->setStatusDescription(Mage::helper('paypal')->__('Error in refunding the payment'));
          }

      }
}
