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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml creditmemo items grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items extends Mage_Core_Block_Template
{
    /**
     * Initialize template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sales/order/creditmemo/create/items.phtml');
    }

    /**
     * Prepare child blocks
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('creditmemo_item_container'),'".$this->getUpdateUrl()."')";
        $this->setChild(
            'update_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => Mage::helper('sales')->__('Update Qty\'s'),
                'class'     => 'update-button',
                'onclick'   => $onclick,
            ))
        );

        $this->setChild(
            'submit_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => Mage::helper('sales')->__('Refund'),
                'class'     => 'save submit-button',
                'onclick'   => 'editForm.submit()',
            ))
        );

        if ($this->getCreditmemo()->canRefund()) {
            /*$this->setChild(
                'submit_offline',
                $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label'     => Mage::helper('sales')->__('Refund Offline'),
                    'class'     => 'save submit-button',
                    'onclick'   => 'editForm.submit()',
                ))
            );*/
        }


        $totalsBlock = $this->getLayout()->createBlock('adminhtml/sales_order_totals')
            ->setTemplate('sales/order/creditmemo/create/totals.phtml')
            ->setSource($this->getCreditmemo())
            ->setCurrency($this->getCreditmemo()->getOrder()->getOrderCurrency());
        $this->setChild('totals', $totalsBlock);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return Mage_Sales_Model_Creditmemo
     */
    public function getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }

    public function canEditQty()
    {
        if ($this->getCreditmemo()->getOrder()->getPayment()->canCapture()) {
            return $this->getCreditmemo()->getOrder()->getPayment()->canCapturePartial();
        }
        return true;
    }

    public function formatPrice($price)
    {
        return $this->getCreditmemo()->getOrder()->formatPrice($price);
    }

    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/updateQty', array('order_id'=>$this->getCreditmemo()->getOrderId()));
    }

    protected function _getQtyBlock()
    {
        $block = $this->getData('_qty_block');
        if (is_null($block)) {
            $block = $this->getLayout()->createBlock('adminhtml/sales_order_item_qty');
            $this->setData('_qty_block', $block);
        }
        return $block;
    }

    public function getQtyHtml($item)
    {
        $html = $this->_getQtyBlock()
            ->setItem($item)
            ->toHtml();
        return $html;
    }
}