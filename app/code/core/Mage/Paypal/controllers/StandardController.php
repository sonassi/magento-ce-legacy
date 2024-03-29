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
 * Paypal Standard Checkout Controller
 *
 */
class Mage_Paypal_StandardController extends Mage_Core_Controller_Front_Action
{
    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

     /**
     * Get singleton with paypal strandard order transaction information
     *
     * @return Mage_Paypal_Model_Standard
     */
    public function getStandard()
    {
        return Mage::getSingleton('paypal/standard');
    }

     /**
     * When a customer chooses Paypal on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('paypal/standard_redirect')->toHtml());
    }

    /*
    * When a customer cancel payment from paypal.
    */
    public function cancelAction()
    {
        //need to save quote as active again if the user click on cacanl payment from paypal
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(true)->save();
        //and then redirect to checkout one page
        $this->_redirect('checkout/cart');
     }

    /*
    * when paypal returns
    * The order information at this point is in POST
    * variables.  However, you don't want to "process" the order until you
    * get validation from the IPN.
    */
    public function  successAction()
    {
        /*
        * set the quote as inactive after back from paypal
        */
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

        //send confirmation email to customer
        $order = Mage::getModel('sales/order');

        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
        if($order->getId()){
            $order->sendNewOrderEmail();
        }

        $this->_redirect('checkout/onepage/success');
    }

    /*
    * when paypal returns via ipn
    * cannot have any output here
    * validate IPN data
    * if data is valid need to update the database that the user has
    */
    public function ipnAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        if($this->getStandard()->getDebug()){
            $debug = Mage::getModel('paypal/api_debug')
                ->setApiEndpoint($this->getStandard()->getPaypalUrl())
                ->setRequestBody(print_r($this->getRequest()->getPost(),1))
                ->save();
        }

        $this->getStandard()->setIpnFormData($this->getRequest()->getPost());
        $this->getStandard()->ipnPostSubmit();
    }
}
