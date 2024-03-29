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
 * Catalog product related items block
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Block_Product_List_Upsell extends Mage_Catalog_Block_Product_Abstract
{
    protected $_columnCount = 4;
    protected $_items;
    
	protected function _prepareData() 
	{
		$collection = Mage::registry('product')->getUpSellProducts()
			->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('thumbnail')
			->addAttributeToSort('position', 'asc')
			->addExcludeProductFilter(Mage::getSingleton('checkout/cart')->getProductIds())
			->useProductItem();
			
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        $collection->load();
        return $this;

	}
	
	protected function	_beforeToHtml()
	{
		$this->_prepareData();
		return parent::_beforeToHtml();
	}
	
	public function getItemCollection()
	{
	    return Mage::registry('product')->getUpSellProducts();
	}
	
	public function getItems() {
	    if (is_null($this->_items)) {
	        $this->_items = $this->getItemCollection()->getItems();
	    }
		return $this->_items;
	}
	
	public function getRowCount()
	{
	    return ceil($this->getItemCollection()->getSize()/$this->getColumnCount());
	}
	
	public function getColumnCount()
	{
	    return $this->_columnCount;
	}
	
	public function resetItemsIterator()
	{
	    $this->getItems();
	    reset($this->_items);
	}
	
	public function getIterableItem()
	{
	    $item = current($this->_items);
	    next($this->_items);
	    return $item;
	}
}// Mage_Catalog_Block_Product_Link_Upsell END