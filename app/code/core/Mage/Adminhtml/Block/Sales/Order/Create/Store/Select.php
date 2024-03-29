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
 * Adminhtml sales order create select store block
 *
 */

class Mage_Adminhtml_Block_Sales_Order_Create_Store_Select extends Mage_Core_Block_Template
{

    protected $_websiteCollection = null;

    public function __construct()
    {
        parent::__construct();
        $this->setId('sc_store_select');
        $this->setTemplate('sales/order/create/store/select.phtml');
    }

    public function getWebsiteCollection()
    {
        if (is_null($this->_websiteCollection)) {
            $this->_websiteCollection = Mage::getModel('core/website')->getResourceCollection()->load();
        }
        return $this->_websiteCollection;
    }

}
