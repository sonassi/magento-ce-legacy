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
 * Adminhtml sales order create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Order_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'sales_order';
        $this->_mode = 'create';

        parent::__construct();

        $this->setId('sales_order_create');

        $this->_updateButton('save', 'label', Mage::helper('sales')->__('Submit Order'));
        $this->_updateButton('save', 'onclick', "order.submit()");

        $this->_removeButton('back');
        
        $confirm = Mage::helper('sales')->__('Are you sure you want to cancel this order?');
        $this->_updateButton('reset', 'label', Mage::helper('sales')->__('Cancel'));
        $this->_updateButton('reset', 'class', 'cancel');
        $this->_updateButton('reset', 'onclick', 'deleteConfirm(\''.$confirm.'\', \'' . $this->getCancelUrl() . '\')');

    }
    
    public function getHeaderHtml()
    {
        $out = '<div id="order:header">';
        $out.= $this->getLayout()->createBlock('adminhtml/sales_order_create_header')->toHtml();
        $out.= '</div>';
        return $out;
    }

    public function getHeaderWidth()
    {
        return 'width: 70%;';
    }
    
    public function getCancelUrl()
    {
        if (Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId()) {
            $url = Mage::getUrl('*/sales_order/view', array(
                'order_id'=>Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId()
            ));
        }
        else {
            $url = Mage::getUrl('*/*/cancel');
        }
        
        return $url;
    }

}
