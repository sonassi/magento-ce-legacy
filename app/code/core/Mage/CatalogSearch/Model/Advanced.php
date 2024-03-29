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
 * @package    Mage_CatalogSearch
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog advanced search model
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 */
class Mage_CatalogSearch_Model_Advanced extends Varien_Object
{
    /**
     * User friendly search criteria list
     *
     * @var array
     */
    private $_searchCriterias = array();

    public function getAttributes()
    {
        $attributes = $this->getData('attributes');
        if (is_null($attributes)) {
            $product = Mage::getModel('catalog/product');
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getConfig()->getId())
                //->addIsSearchableFilter()
                ->addHasOptionsFilter()
                ->addDisplayInAdvancedSearchFilter()
                ->setOrder('attribute_id', 'asc')
                ->load();
            foreach ($attributes as $attribute) {
            	$attribute->setEntity($product->getResource());
            }
            $this->setData('attributes', $attributes);
        }
        return $attributes;
    }

    public function addFilters($values){
        $attributes = $this->getAttributes();
        $allConditions = array();

        foreach ($attributes as $attribute) {
            $code      = $attribute->getAttributeCode();
            $condition = false;

            if (isset($values[$code])) {
                $value = $values[$code];

                if (is_array($value)) {
                    if ((isset($value['from']) && strlen($value['from']) > 0) || (isset($value['to']) && strlen($value['to']) > 0)) {
                        $condition = $value;
                    } elseif (!isset($value['from']) && !isset($value['to'])) {
                        $condition = array('in'=>$value);
                    }
                } else {
                    if (strlen($value)>0) {
                        if (in_array($attribute->getBackend()->getType(), array('varchar', 'text'))) {
                            $condition = array('like'=>'%'.$value.'%');
                        } else {
                            $condition = $value;
                        }
                    }
                }
            }

            if ($condition) {
                $table = $attribute->getBackend()->getTable();
                $attributeId = $attribute->getId();
                if ($attribute->getBackendType() == 'static'){
                    $attributeId = $attribute->getAttributeCode();
                    $condition = array('like'=>"%{$condition}%");
                }

                $allConditions[$table][$attributeId] = $condition;

                $this->addSearchCriteria($attribute, $value);
            }
        }
        if ($allConditions) {
            $this->getProductCollection()->addFieldsToFilter($allConditions);
        } else {
            Mage::throwException(Mage::helper('catalogsearch')->__('You have to specify at least one search criteria'));
        }

        return $this;
    }

    private function addSearchCriteria($attribute, $value)
    {
        $name = $attribute->getFrontend()->getLabel();

        if (is_array($value) && (isset($value['from']) || isset($value['to']))){
            if ($value['from'] > 0 && $value['to'] > 0) {
                // -
                $value = sprintf('%s - %s', $value['from'], $value['to']);
            } elseif ($value['from'] > 0) {
                // and more
                $value = Mage::helper('catalogsearch')->__('%s and greater', $value['from']);
            } elseif ($value['to'] > 0) {
                // to
                $value = Mage::helper('catalogsearch')->__('up to %s', $value['to']);
            }
        }

        if ($attribute->getFrontendInput() == 'select' && is_array($value)) {
            foreach ($value as $k=>$v){
                $value[$k] = $attribute->getSource()->getOptionText($v);
            }
            $value = implode(', ', $value);
        } else if ($attribute->getFrontendInput() == 'select') {
            $value = $attribute->getSource()->getOptionText($value);
            $value = $value['label'];
        }

        $this->_searchCriterias[] = array('name'=>$name, 'value'=>$value);
    }

    public function getSearchCriterias()
    {
        return $this->_searchCriterias;
    }

    public function getProductCollection(){
        if (is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getResourceModel('catalogsearch/advanced_collection')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image');
                Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);
                Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);
        }

        return $this->_productCollection;
    }
}
