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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog comapare sidebar block
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
 class Mage_Catalog_Block_Product_Compare_Sidebar extends Mage_Core_Block_Template
 {
 	protected function _construct()
 	{
 		$this->setId('compare');
 	}

 	public function getItems()
 	{
 		return $this->helper('catalog/product_compare')->getItemCollection();
 	}

    public function getRemoveUrl($item)
    {
        return $this->helper('catalog/product_compare')->getRemoveUrl($item);
    }

    public function getClearUrl()
    {
        return $this->helper('catalog/product_compare')->getClearListUrl();
    }

 	public function getCompareUrl()
 	{
 	    return $this->helper('catalog/product_compare')->getListUrl();
 	}
 } // Class Mage_Catalog_Block_Compare_Sidebar end