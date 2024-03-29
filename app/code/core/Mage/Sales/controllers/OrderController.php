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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales orders controller
 *
 * @category   Mage
 * @package    Mage_Sales
 */

class Mage_Sales_OrderController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * Customer order history
     */
    public function historyAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check order view availability
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($order->getId() && $order->getCustomerId() && $order->getCustomerId() == $customerId) {
            return true;
        }
        return false;
    }

    /**
     * Order view page
     */
    public function viewAction()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');

        $order = Mage::getModel('sales/order')->load($orderId);

        $method = explode('_', $order->getShippingMethod());

        if ($method[0]!='') {
            $order->tracking = Mage::getSingleton('shipping/shipping')->getCarrierByCode($method[0])->isTrackingAvailable();
        } else {
            $this->_redirect('*/*/history');
        }
        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);

            $this->loadLayout();
            if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
                $navigationBlock->setActive('sales/order/history');
            }
            $this->renderLayout();
        }
        else {
            $this->_redirect('*/*/history');
        }
    }

    public function invoiceAction()
    {
        //var_dump($this->getRequest());

        $orderId = (int) $this->getRequest()->getParam('order_id');

        $order = Mage::getModel('sales/order')->load($orderId);

        $method = explode('_', $order->getShippingMethod());

        if ($method[0]!='') {
            $order->tracking = Mage::getSingleton('shipping/shipping')->getCarrierByCode($method[0])->isTrackingAvailable();
        } else {
            $this->_redirect('*/*/history');
        }
        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);

            $this->loadLayout();
            if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
                $navigationBlock->setActive('sales/order/history');
            }
            $this->renderLayout();
        }
        else {
            $this->_redirect('*/*/history');
        }
    }

    public function shipmentAction()
    {
        //var_dump($this->getRequest());

        $orderId = (int) $this->getRequest()->getParam('order_id');

        $order = Mage::getModel('sales/order')->load($orderId);

        $method = explode('_', $order->getShippingMethod());

        if ($method[0]!='') {
            $order->tracking = Mage::getSingleton('shipping/shipping')->getCarrierByCode($method[0])->isTrackingAvailable();
        } else {
            $this->_redirect('*/*/history');
        }
        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);

            $this->loadLayout();
            if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
                $navigationBlock->setActive('sales/order/history');
            }
            $this->renderLayout();
        }
        else {
            $this->_redirect('*/*/history');
        }
    }

    public function creditmemoAction()
    {
        //var_dump($this->getRequest());

        $orderId = (int) $this->getRequest()->getParam('order_id');

        $order = Mage::getModel('sales/order')->load($orderId);

        $method = explode('_', $order->getShippingMethod());

        if ($method[0]!='') {
            $order->tracking = Mage::getSingleton('shipping/shipping')->getCarrierByCode($method[0])->isTrackingAvailable();
        } else {
            $this->_redirect('*/*/history');
        }
        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);

            $this->loadLayout();
            if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
                $navigationBlock->setActive('sales/order/history');
            }
            $this->renderLayout();
        }
        else {
            $this->_redirect('*/*/history');
        }
    }

    public function reorderAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {

            Mage::register('current_order', $order);

            $cart = Mage::getSingleton('checkout/cart');
            $cartTruncated = false;


            $items = $order->getItemsCollection();
            foreach ($items as $item){

                $productId = (int) $item->getProductId();
                $qty       = (int) $item->getQtyOrdered();

                $product = Mage::getModel('catalog/product')
                    ->load($productId);
                try {
                    $cart->addProduct($product, $qty);

                } catch (Mage_Core_Exception $e){
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    }
                    else {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }

                    $this->_redirect('*/*/history');

                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Can not add item to shopping cart'));
                    $this->_redirect('checkout/cart');
                }

            }

            $cart->save();


            $this->_redirect('checkout/cart');
        }
        else {
            $this->_redirect('*/*/history');
        }
    }

    public function detailsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function printAction()
    {
        $this->loadLayout('print');
        $this->renderLayout();
    }
}