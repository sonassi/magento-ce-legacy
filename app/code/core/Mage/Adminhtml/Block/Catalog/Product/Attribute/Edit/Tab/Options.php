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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute add/edit form options tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/attribute/options.phtml');
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete'),
                    'class' => 'delete delete-option'
                )));
                
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add Option'),
                    'class' => 'add',
                    'id'    => 'add_new_option_button'
                )));
        return parent::_prepareLayout();
    }
    
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
    
    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }
    
    public function getOptionValues()
    {
        $values = $this->getData('option_values');
        if (is_null($values)) {
            $values = array();
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setPositionOrder('desc')
                ->load();
                
            foreach ($optionCollection as $option) {
                $value = array();
                $value['id'] = $option->getId();
                $value['sort_order'] = $option->getSortOrder();
                foreach ($this->getStores() as $store) {
                    $storeValues = $this->getStoreOptionValues($store->getId());
                    if (isset($storeValues[$option->getId()])) {
                        $value['store'.$store->getId()] = htmlspecialchars($storeValues[$option->getId()]);
                    }
                    else {
                        $value['store'.$store->getId()] = '';
                    }
                }
                $values[] = new Varien_Object($value);
            }
            $this->setData('option_values', $values);
        }
        
        return $values;
    }
    
    public function getLabelValues()
    {
        $values = array();
        $values[0] = $this->getAttributeObject()->getFrontend()->getLabel();
        $translations = Mage::getModel('core/translate_string')
           ->load($this->getAttributeObject()->getFrontend()->getLabel())
           ->getStoreTranslations();
        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($translations[$store->getId()]) ? $translations[$store->getId()] : '';
            }
        }
        return $values;
    }
    
    public function getStoreOptionValues($storeId)
    {
        $values = $this->getData('store_option_values_'.$storeId);
        if (is_null($values)) {
            $values = array();
            $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setStoreFilter($storeId, false)
                ->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
            }
            $this->setData('store_option_values_'.$storeId, $values);
        }
        return $values;
    }
    
    public function getAttributeObject()
    {
        return Mage::registry('entity_attribute');
    }
}