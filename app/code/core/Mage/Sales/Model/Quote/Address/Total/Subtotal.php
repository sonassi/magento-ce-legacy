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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Quote_Address_Total_Subtotal extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Collect address subtotal
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $address->setSubtotal(0);

        $items = $address->getAllItems();
        if (count($items)) {
            foreach ($items as $item) {
            	if (!$this->_initItem($address, $item) || $item->getQty()<=0) {
            	    $this->_removeItem($address, $item);
            	}
            }
        }

        $address->setGrandTotal($address->getSubtotal());
        return $this;
    }

    /**
     * Address item initialization
     *
     * @param  $item
     * @return bool
     */
    protected function _initItem($address, $item)
    {
    	if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
    	    $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
    	}
    	else {
    	    $quoteItem = $item;
    	}
    	$product = $quoteItem->getProduct();
    	$superProduct = $quoteItem->getSuperProduct();

    	if (!$product || !$product->isVisibleInCatalog() || ($superProduct && !$superProduct->isVisibleInCatalog())) {
            return false;
        }

    	$item->setPrice($product->getFinalPrice($quoteItem->getQty()));
    	$item->calcRowTotal();
        $address->setSubtotal($address->getSubtotal() + $item->getRowTotal());
        return true;
    }

    /**
     * Remove item
     *
     * @param  $address
     * @param  $item
     * @return Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    protected function _removeItem($address, $item)
    {
	    if ($item instanceof Mage_Sales_Model_Quote_Item) {
	        $address->removeItem($item->getId());
            if ($address->getQuote()) {
                $address->getQuote()->removeItem($item->getId());
            }
	    }
	    elseif ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
	        $address->removeItem($item->getId());
            if ($address->getQuote()) {
                $address->getQuote()->removeItem($item->getQuoteItemId());
            }
	    }

	    return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
            'code'=>$this->getCode(),
            'title'=>Mage::helper('sales')->__('Subtotal'),
            'value'=>$address->getSubtotal()
        ));

        return $this;
    }
}