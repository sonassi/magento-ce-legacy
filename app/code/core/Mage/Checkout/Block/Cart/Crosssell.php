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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cart crosssell list
 *
 * @category   Mage
 * @package    Mage_Checkout
 */
class Mage_Checkout_Block_Cart_Crosssell extends Mage_Catalog_Block_Product_Abstract
{
    protected $_maxItemCount = 3;
    
    public function getItems()
    {
        $items = $this->getData('items');
        if (is_null($items)) {
            $items = array();
            $ninProductIds = $this->_getCartProductIds();
            if ($lastAdded = (int) $this->_getLastAddedProductId()) {
                $collection = $this->_getCollection()
                    ->addFieldToFilter('product_id', $lastAdded);
                if (!empty($ninProductIds)) {
                    $collection->addFieldToFilter('linked_product_id', array('nin'=>$ninProductIds));
                }
                $collection->load();
                
                foreach ($collection as $item) {
                    $ninProductIds[] = $item->getId();
                	$items[] = $item;
                }
            }
            
            if (count($items)<$this->_maxItemCount) {
                $collection = $this->_getCollection()
                    ->addFieldToFilter('product_id', $this->_getCartProductIds());
                if (!empty($ninProductIds)) {
                    $collection->addFieldToFilter('linked_product_id', array('nin'=>$ninProductIds));
                }
                $collection->setPageSize($this->_maxItemCount-count($items));
                $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                $collection->load();
                foreach ($collection as $item) {
                	$items[] = $item;
                }                    
            }
            
            $this->setData('items', $items);
        }
        return $items;
    }
    
    public function getItemCount()
    {
        return count($this->getItems());
    }
    
    protected function _getCartProductIds()
    {
        $ids = $this->getData('_cart_product_ids');
        if (is_null($ids)) {
            $ids = array();
            foreach ($this->getQuote()->getAllItems() as $item) {
            	if ($product = $item->getProduct()) {
            	    $ids[] = $product->getId();
            	    if ($superProduct = $product->getSuperProduct()) {
            	        $ids[] = $superProduct->getId();
            	    }
            	}
            }
            $this->setData('_cart_product_ids', $ids);
        }
        return $ids;
    }
    
    protected function _getLastAddedProductId()
    {
        return Mage::getSingleton('checkout/session')->getLastAddedProductId(true);
    }
    
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
    
    protected function _getCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_link_collection')
            ->setObject('catalog/product')
            ->setLinkType('cross_sell')
            ->joinLinkTable()
			->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('thumbnail')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addLinkTypeFilter()
            ->addStoreFilter()
            ->setPageSize($this->_maxItemCount);
            
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        return $collection;
    }
}
