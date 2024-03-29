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
 * Adminhtml sales order create newsletter block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Order_Create_Newsletter extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_newsletter');
    }

    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Newsletter Subscription');
    }

    public function getHeaderCssClass()
    {
        return 'head-newsletter-list';
    }

    protected function _prepareLayout()
    {
        $this->setChild('form', $this->getLayout()->createBlock('adminhtml/sales_order_create_newsletter_form'));
        return parent::_prepareLayout();
    }

    public function toHtml()
    {
        if (! Mage::getSingleton('adminhtml/quote')->getIsOldCustomer()) {
            return parent::toHtml();
        }
        return '';
    }

}
