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
 * Adminhtml dashboard helper for orders
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Helper_Dashboard_Order extends Mage_Adminhtml_Helper_Dashboard_Abstract
{
    protected function _initCollection()
    {
        $this->_collection = Mage::getResourceSingleton('reports/order_collection')
            ->prepareSummary($this->getParam('range'), $this->getParam('custom_from'), $this->getParam('custom_to'), $this->getParam('store'));

        if($this->getParam('store')) {
            $this->_collection->addAttributeToFilter('store_id', $this->getParam('store'));
        }

        $this->_collection->load();
    }
} // Class Mage_Adminhtml_Helper_Dashboard_Order end