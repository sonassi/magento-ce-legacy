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
 * PayPal Direct Module
 *
 */
class Mage_Paypal_Model_Direct extends Mage_Payment_Model_Method_Cc
{
    protected $_code  = 'paypal_direct';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;

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

    public function getPaymentAction()
    {
        $paymentAction = Mage::getStoreConfig('payment/paypal_direct/payment_action');
        if (!$paymentAction) {
            $paymentAction = Mage_Paypal_Model_Api_Nvp::PAYMENT_TYPE_AUTH;
        }
        return $paymentAction;
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        $api = $this->getApi()
            ->setPaymentType($this->getPaymentAction())
            ->setAmount($amount)
            ->setBillingAddress($payment->getOrder()->getBillingAddress())
            ->setPayment($payment);
        ;

        if ($api->callDoDirectPayment()!==false) {
            $payment
                ->setStatus('APPROVED')
                ->setCcTransId($api->getTransactionId())
                ->setCcAvsStatus($api->getAvsCode())
                ->setCcCidStatus($api->getCvv2Match());

            #$payment->getOrder()->addStatusToHistory(Mage::getStoreConfig('payment/paypal_direct/order_status'));
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

    public function capture(Varien_Object $payment, $amount)
    {
        $api = $this->getApi()
            ->setPaymentType(Mage_Paypal_Model_Api_Nvp::PAYMENT_TYPE_SALE)
            ->setAmount($amount)
            ->setBillingAddress($payment->getOrder()->getBillingAddress())
            ->setPayment($payment);
        ;
        if ($payment->getCcTransId()) {
            $api->setAuthorizationId($payment->getCcTransId())
                ->setCompleteType('NotComplete');
            $result = $api->callDoCapture()!==false;
        } else {
            $result = $api->callDoDirectPayment()!==false;
        }
        if ($result) {
            $payment
                ->setStatus('APPROVED')
                ->setCcTransId($api->getTransactionId())
                ->setCcAvsStatus($api->getAvsCode())
                ->setCcCidStatus($api->getCvv2Match());

            #$payment->getOrder()->addStatusToHistory(Mage::getStoreConfig('payment/paypal_direct/order_status'));
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

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
        $api = $this->getApi()
            ->setPaymentType($this->getPaymentAction())
            ->setAmount($payment->getOrder()->getGrandTotal())
            ->setBillingAddress($payment->getOrder()->getBillingAddress())
            ->setPayment($payment);
        ;

        if ($api->callDoDirectPayment()!==false) {
            $payment
                ->setStatus('APPROVED')
                ->setCcTransId($api->getTransactionId())
                ->setCcAvsStatus($api->getAvsCode())
                ->setCcCidStatus($api->getCvv2Match());

            #$payment->getOrder()->addStatusToHistory(Mage::getStoreConfig('payment/paypal_direct/order_status'));
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
            $payment
                ->setStatus('ERROR')
                ->setStatusDescription($message);
        }
        return $this;
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