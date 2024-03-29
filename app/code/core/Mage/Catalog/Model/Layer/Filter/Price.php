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
 * Layer price filter
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Abstract 
{
    const MIN_RANGE_POWER = 10;
    
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'price';
    }
    
    /**
     * Apply price filter to collection
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) 
    {
        /**
         * Filter must be string: $index,$range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
        
        $filter = explode(',', $filter);
        if (count($filter) != 2) {
            return $this;
        }
        
        list($index, $range) = $filter;
        
        if ((int)$index && (int)$range) {
            $this->setPriceRange((int)$range);
            //$range = $this->getPriceRange();
            $this->getLayer()->getProductCollection()
                ->addFieldToFilter('price', array(
                    'from'  => ($index-1)*$range,
                    'to'    => $index*$range-0.001,
                ));
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderItemLabel($range, $index), $filter)
            );
            $this->_items = array();
        }
        return $this;
    }
    
    public function getName()
    {
        return Mage::helper('catalog')->__('Price');
    }

    /**
     * Retrieve price range for build filter
     *
     * @return int
     */
    public function getPriceRange()
    {
        $range = $this->getData('price_range');
        if (is_null($range)) {
            $maxPrice = $this->getMaxPriceInt();
            $index = 1;
            do {
                $range = pow(10, (strlen(floor($maxPrice))-$index));
                $items = $this->getRangeItemCounts($range);
                $index++;
            }
            while($range>self::MIN_RANGE_POWER && count($items)<2);
            
            $this->setData('price_range', $range);
        }
        return $range;
    }
    
    public function getMaxPriceInt()
    {
        $maxPrice = $this->getData('max_price_int');
        if (is_null($maxPrice)) {
            $maxPrice = Mage::getSingleton('catalog/layer')->getProductCollection()
                ->getMaxAttributeValue('price');
            $maxPrice = floor($maxPrice);
            $this->setData('max_price_int', $maxPrice);
        }
        return $maxPrice;
    }
    
    public function getRangeItemCounts($range)
    {
        $items = $this->getData('range_item_counts_'.$range);
        if (is_null($items)) {
            $items = Mage::getSingleton('catalog/layer')->getProductCollection()
                ->getAttributeValueCountByRange('price', $range);
            $this->setData('range_item_counts_'.$range, $items);
        }
        return $items;
    }
    
    /**
     * Retrieve filter items
     *
     * @return array
     */
    protected function _initItems()
    {
        $range      = $this->getPriceRange();
        $dbRanges   = $this->getRangeItemCounts($range);
        $items = array();
        
        foreach ($dbRanges as $index=>$count) {
            $value = $index . ',' . $range;
        	$items[] = $this->_createItem($this->_renderItemLabel($range, $index), $value, $count);
        }
        
        $this->_items = $items;
        return $this;
    }
    
    protected function _renderItemLabel($range, $value)
    {
        $store = Mage::app()->getStore();
        return $store->convertPrice(($value-1)*$range, true).' - '.$store->convertPrice($value*$range, true);
    }
}
