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
 * @package    Mage_Wishlist
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base wishlist helper
 *
 */
class Mage_Wishlist_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_wishlist = null;
    protected $_itemCount = null;
    protected $_itemCollection = null;
    protected $_productCollection = null;

    /**
     * Retrieve customer login status
     *
     * @return bool
     */
    protected function _isCustomerLogIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Retrieve logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCurrentCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Retrieve wishlist by logged in customer
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getWishlist()
    {
        if (is_null($this->_wishlist)) {
            $this->_wishlist = Mage::getModel('wishlist/wishlist')
				->loadByCustomer($this->_getCurrentCustomer());
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve wishlist items availability
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->getItemCount();
    }

    /**
     * Retrieve wishlist item count
     *
     * @return int
     */
    public function getItemCount()
    {
        if ($this->_isCustomerLogIn() && is_null($this->_itemCount)) {
            $this->_itemCount = $this->getWishlist()->getItemsCount();
        }
        elseif(is_null($this->_itemCount)) {
            $this->_itemCount = 0;
        }
        return $this->_itemCount;
    }

    /**
     * Retrieve wishlist items collection
     *
     * @return
     */
    public function getItemCollection()
    {
        if (is_null($this->_itemCollection)) {
            $this->_itemCollection = $this->getWishlist()->getProductCollection();
        }
        return $this->_itemCollection;
    }

    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getWishlist()->getProductCollection();
        }
        return $this->_productCollection;
    }


    public function getSharingUrl()
    {

    }

    /**
     * Retrieve url for removing item from wishlist
     *
     * @param   Mage_Wishlist_Model_Item $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        return $this->_getUrl('wishlist/index/remove', array('item'=>$item->getWishlistItemId()));
    }

    /**
     * Retrieve url for adding product to wishlist
     *
     * @param   mixed $product
     * @return  string
     */
    public function getAddUrl($item)
    {
        if ($item instanceof Mage_Catalog_Model_Product) {
            return $this->_getUrl('wishlist/index/add', array('product'=>$item->getId()));
        }
        if ($item instanceof Mage_Wishlist_Model_Item) {
            return $this->_getUrl('wishlist/index/add', array('product'=>$item->getProductId()));
        }
        return false;
    }

    /**
     * Retrieve url for adding item to shoping cart
     *
     * @param   Mage_Wishlist_Model_Item $item
     * @return  string
     */
    public function getAddToCartUrl($item)
    {
        return $this->_getUrl('wishlist/index/cart', array('item'=>$item->getWishlistItemId()));
    }

    /**
     * Retrieve customer wishlist url
     *
     * @return string
     */
    public function getListUrl()
    {
        return $this->_getUrl('wishlist');
    }

    public function isAllow()
    {
        if (Mage::getStoreConfig('wishlist/general/active')) {
			return true;
		}
		return false;
    }
}
