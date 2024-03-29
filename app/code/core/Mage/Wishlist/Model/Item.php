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
 * Wishlist item model
 *
 * @category   Mage
 * @package    Mage_Wishlist
 */

class Mage_Wishlist_Model_Item extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('wishlist/item');
    }
    
    public function getDataForSave()
    {
        $data = array();
        $data['product_id']  = $this->getProductId();
        $data['wishlist_id'] = $this->getWishlistId();
        $data['added_at']    = $this->getAddedAt() ? $this->getAddedAt() : now();
        $data['description'] = $this->getDescription();
        $data['store_id']    = $this->getStoreId() ? $this->getStoreId() : Mage::app()->getStore()->getId();
        
        return $data;
    }
    
    public function loadByProductWishlist($wishlistId, $productId, $sharedStores) 
    {
        $this->_getResource()->loadByProductWishlist($this, $wishlistId, $productId, $sharedStores);
        return $this;
    }   
}// Class Mage_Wishlist_Model_Item END