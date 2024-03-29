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
 * Product compare helper
 *
 */
class Mage_Catalog_Helper_Product_Compare extends Mage_Core_Helper_Url
{
    protected $_itemCollection;

    /**
     * Retrieve compare list url
     *
     * @return string
     */
    public function getListUrl()
    {
 	    $itemIds = array();
 	    foreach ($this->getItemCollection() as $item) {
 	    	$itemIds[] = $item->getId();
 	    }

 	    $params = array(
            'items'=>implode(',', $itemIds),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
 	    );

 		return $this->_getUrl('catalog/product_compare', $params);
    }

    /**
     * Retrieve url for adding product to conpare list
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  string
     */
    public function getAddUrl($product)
    {
	    if ($product->isSuper()) {
	        return false;
	    }

	    $params = array(
            'product'=>$product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
        );
	    return $this->_getUrl('catalog/product_compare/add', $params);
    }

    /**
     * Retrive add to wishlist url
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToWishlistUrl($product)
    {
        $beforeCompareUrl = base64_encode(Mage::getSingleton('catalog/session')->getBeforeCompareUrl());

        $params = array(
            'product'=>$product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $beforeCompareUrl
        );

        return $this->_getUrl('wishlist/index/add', $params);
    }

    /**
     * Retrive add to cart url
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToCartUrl($product)
    {
        $params = array(
            'product'=>$product->getId()
        );

        return $this->_getUrl('checkout/cart/add', $params);
    }

    /**
     * Retrieve remove item from compare list url
     *
     * @param   $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        $params = array(
            'product'=>$item->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
        );
        return $this->_getUrl('catalog/product_compare/remove', $params);
    }

    /**
     * Retrieve clear compare list url
     *
     * @return string
     */
    public function getClearListUrl()
    {
        $params = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
        );
        return $this->_getUrl('catalog/product_compare/clear', $params);
    }

    /**
     * Retrieve compare list items collection
     *
     * @return
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
 			$this->_itemCollection = Mage::getResourceModel('catalog/product_compare_item_collection')
 				->setStoreId(Mage::app()->getStore()->getId());

 			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
				$this->_itemCollection->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
			} else {
				$this->_itemCollection->setVisitorId(Mage::getSingleton('log/visitor')->getId());
			}

			$this->_itemCollection->addAttributeToSelect('name')
				->useProductItem()
				->load();
        }
        return $this->_itemCollection;
    }

    /**
     * Retrieve count of items in compare list
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->getItemCollection()->getSize();
    }
}
