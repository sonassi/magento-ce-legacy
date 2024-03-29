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
 * Order view tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Sales_Order_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_view_tabs');
        $this->setDestElementId('sales_order_view');
        $this->setTitle(Mage::helper('sales')->__('Order View'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('order_info', array(
            'label'     => Mage::helper('sales')->__('Information'),
            'title'     => Mage::helper('sales')->__('Order Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_info')->toHtml(),
            'active'    => true
        ));

        /*$this->addTab('order_history', array(
            'label'     => Mage::helper('catalogrule')->__('History'),
            'title'     => Mage::helper('catalogrule')->__('Order History'),
            'content'   => 'Order History',
        ));*/

        $this->addTab('order_invoices', array(
            'label'     => Mage::helper('catalogrule')->__('Invoices'),
            'title'     => Mage::helper('catalogrule')->__('Order Invoices'),
            'content'   => $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_invoices')->toHtml(),
        ));

        $this->addTab('order_creditmemos', array(
            'label'     => Mage::helper('catalogrule')->__('Credit Memos'),
            'title'     => Mage::helper('catalogrule')->__('Order Credit Memos'),
            'content'   => $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_creditmemos')->toHtml(),
        ));

        $this->addTab('order_shipments', array(
            'label'     => Mage::helper('catalogrule')->__('Shipments'),
            'title'     => Mage::helper('catalogrule')->__('Order Shipments'),
            'content'   => $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_shipments')->toHtml(),
        ));

        /*$this->addTab('order_giftmessages', array(
            'label'     => Mage::helper('catalogrule')->__('Gift Messages'),
            'title'     => Mage::helper('catalogrule')->__('Order Gift Messages'),
            'content'   => 'Gift Messages',
        ));*/
        return parent::_beforeToHtml();
    }
}