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
 * Adminhtml sales order create coupons block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Order_Create_Coupons extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_coupons_form');
        $this->setTemplate('sales/order/create/coupons/form.phtml');
    }

    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }
    
    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Coupons');
    }

    public function getHeaderCssClass()
    {
        return 'head-promo-quote';
    }

    protected function _prepareLayout()
    {
        $this->setChild('form', $this->getLayout()->createBlock('adminhtml/sales_order_create_coupons_form'));
        return parent::_prepareLayout();
    }

    public function toHtml()
    {
        return parent::toHtml();
    }
}