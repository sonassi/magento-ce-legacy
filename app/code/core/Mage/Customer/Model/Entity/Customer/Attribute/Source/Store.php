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
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer store attribute source
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Mage_Customer_Model_Entity_Customer_Attribute_Source_Store extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $collection = Mage::getResourceModel('core/store_collection');
            if ('store_id' == $this->getAttribute()->getAttributeCode()) {
                $collection->setWithoutDefaultFilter();
            }
            $this->_options = $collection->load()->toOptionArray();
            if ('created_in' == $this->getAttribute()->getAttributeCode()) {
                array_unshift($this->_options, array('value' => '0', 'label' => Mage::helper('customer')->__('Admin')));
            }
        }
        return $this->_options;
    }
    
    public function getOptionText($value)
    {
        if(!$value)$value ='0';
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }
        
        if (!$this->_options) {
            $collection = Mage::getResourceModel('core/store_collection');
            if ('store_id' == $this->getAttribute()->getAttributeCode()) {
                $collection->setWithoutDefaultFilter();
            }
            $this->_options = $collection->load()->toOptionArray();
            if ('created_in' == $this->getAttribute()->getAttributeCode()) {
                array_unshift($this->_options, array('value' => '0', 'label' => Mage::helper('customer')->__('Admin')));
            }
        }
        
        if ($isMultiple) {
            $values = array();
            foreach ($value as $val) {
                $values[] = $this->_options[$val];
            }
            return $values;
        }
        else {
            return $this->_options[$value];
        }
        return false;
    }
}