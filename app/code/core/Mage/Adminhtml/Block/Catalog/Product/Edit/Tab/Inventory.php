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
 * Product inventory data
 *
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/tab/inventory.phtml');
    }

    public function getBackordersOption()
    {
        return Mage::getSingleton('cataloginventory/source_backorders')->toOptionArray();
    }

    public function getStockItem()
    {
        return Mage::registry('product')->getStockItem();
    }

    public function isConfigurable()
    {
        return Mage::registry('product')->isConfigurable();
    }

    public function getFieldValue($field)
    {
        if ($this->getStockItem()) {
            return $this->getStockItem()->getData($field);
        }
        return null;
    }

    public function isNew()
    {
        if (Mage::registry('product')->getId()) {
            return false;
        }
        return true;
    }
}
