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
 * Catalog compare item model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Product_Compare_Item extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('catalog/product_compare_item');
    }
    
    public function addCustomerData(Mage_Customer_Model_Customer $customer)
    {
        $this->setCustomerId($customer->getId());
        $this->setVisitorId(0);
        return $this;
    }
    
    public function addVisitorId($visitorId)
    {
        $this->setVisitorId($visitorId);
        return $this;
    }
    
    public function loadByProduct($product)
    {
        $this->_getResource()->loadByProduct($this,$product);        
        return $this;
    }
    
    public function addProductData($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $this->setProductId($product->getId());
        }
        elseif(intval($product)) {
            $this->setProductId(intval($product));
        }
        
        return $this;
    }
    
    public function getDataForSave()
    {
        $data = array();
        $data['customer_id'] = $this->getCustomerId();
        $data['visitor_id']  = $this->getVisitorId();
        $data['product_id']  = $this->getProductId();
        
        return $data;
    }
    
    public function bindCustomerLogin()
    {
        $collectionVisitor = Mage::getResourceModel('catalog/product_compare_item_collection');
        $collectionVisitor
            ->setVisitorId(Mage::getSingleton('log/visitor')->getId())
            ->load();
        
        $session = Mage::getSingleton('customer/session');
            
        $collectionCustomer = $this->getResourceCollection()
            ->setCustomerId($session->getCustomerId())
            ->load();;
                
        
        
        $collectionVisitor->walk('addCustomerData', array($session->getCustomer()));
        $collectionCustomerIds = $collectionCustomer->getProductIds();
        foreach($collectionVisitor as $item) {
            try {
                if(in_array($item->getProductId(), $collectionCustomerIds)) {
                    $item->delete();
                } else {
                    $item->save();
                }
            } 
            catch (Exception $e) {
                //
            }
        }
        return $this;
    }
}// Class Mage_Catalog_Model_Compare_Item END