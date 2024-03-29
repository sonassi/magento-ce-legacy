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
 * Order view messages
 *
 */
class Mage_Adminhtml_Block_Sales_Order_View_Messages extends Mage_Core_Block_Messages
{
    protected function _getOrder()
    {
        return Mage::registry('sales_order');
    }

    public function _prepareLayout()
    {
        /**
         * Check customer existing
         */
        $customer = Mage::getModel('customer/customer')->load($this->_getOrder()->getCustomerId());
        if (!$customer->getId()) {
            //$this->addNotice(Mage::helper('sales')->__(' The customer doesn\'t exist in the system anymore'));
        }

        /**
         * Check Item products existing
         */
        $productIds = array();
        foreach ($this->_getOrder()->getAllItems() as $item) {
        	$productIds[] = $item->getProductId();
        }

        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addIdFilter($productIds)
            ->load();

        foreach ($this->_getOrder()->getAllItems() as $item) {
        	if (!$productCollection->getItemById($item->getProductId())) {
        	    /*$this->addNotice(
                    Mage::helper('sales')->__('The item %s (SKU %s) doesn\'t exist in the catalog anymore', $item->getName(), $item->getSku())
        	    );*/
        	}
        }
        return parent::_prepareLayout();
    }
}
