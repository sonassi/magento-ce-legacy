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
 * Catalog super product attribute pricing model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Product_Super_Attribute_Pricing extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		$this->_init('catalog/product_super_attribute_pricing');
	}
	
	public function getDataForSave()
	{
		return $this->toArray(array('product_super_attribute_id','value_index','is_percent','pricing_value'));
	}
	
	public function setPricingLabelFromAttribute(Mage_Eav_Model_Entity_Attribute_Abstract $attribute) 
	{
		if($attribute->getSource()) {
			$this->setLabel($attribute->getSource()->getOptionText($this->getValueIndex()));
		} else {
			$this->setLabel($this->getValueIndex());
		}
		return $this;
	}
}// Class Mage_Catalog_Model_Product_Super_Attribute_Pricing END