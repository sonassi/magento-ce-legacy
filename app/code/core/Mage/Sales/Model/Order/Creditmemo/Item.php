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

class Mage_Sales_Model_Order_Creditmemo_Item extends Mage_Core_Model_Abstract
{
    protected $_creditmemo = null;
    protected $_orderItem = null;

    /**
     * Initialize resource model
     */
    function _construct()
    {
        $this->_init('sales/order_creditmemo_item');
    }

    /**
     * Declare creditmemo instance
     *
     * @param   Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return  Mage_Sales_Model_Order_Creditmemo_Item
     */
    public function setCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $this->_creditmemo = $creditmemo;
        return $this;
    }

    /**
     * Retrieve creditmemo instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_creditmemo;
    }

    /**
     * Declare order item instance
     *
     * @param   Mage_Sales_Model_Order_Item $item
     * @return  Mage_Sales_Model_Order_Creditmemo_Item
     */
    public function setOrderItem(Mage_Sales_Model_Order_Item $item)
    {
        $this->_orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Retrieve order item instance
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function getOrderItem()
    {
        if (is_null($this->_orderItem)) {
            if ($this->getCreditmemo()) {
                $this->_orderItem = $this->getCreditmemo()->getOrder()->getItemById($this->getOrderItemId());
            }
            else {
                $this->_orderItem = Mage::getModel('sales/order_item')
                    ->load($this->getOrderItemId());
            }
        }
        return $this->_orderItem;
    }

    /**
     * Declare qty
     *
     * @param   float $qty
     * @return  Mage_Sales_Model_Order_Creditmemo_Item
     */
    public function setQty($qty)
    {
        $qty = (float) $qty;
        $qty = $qty > 0 ? $qty : 0;
        /**
         * Check qty availability
         */
        if ($qty <= $this->getOrderItem()->getQtyToRefund()) {
            $this->setData('qty', $qty);
        }
        else {
            Mage::throwException(
                Mage::helper('sales')->__('Invalid qty to refund item "%s"', $this->getName())
            );
        }
        return $this;
    }

    /**
     * Applying qty to order item
     *
     * @return Mage_Sales_Model_Order_Shipment_Item
     */
    public function register()
    {
        $this->getOrderItem()->setQtyRefunded(
            $this->getOrderItem()->getQtyRefunded()+$this->getQty()
        );
        return $this;
    }

    public function cancel()
    {
        $this->getOrderItem()->setQtyRefunded(
            $this->getOrderItem()->getQtyRefunded()-$this->getQty()
        );
        return $this;
    }

    /**
     * Invoice item row total calculation
     *
     * @return Mage_Sales_Model_Order_Invoice_Item
     */
    public function calcRowTotal()
    {
        $rowTotal = $this->getPrice()*$this->getQty();
        $this->setRowTotal($rowTotal);
        return $this;
    }
}