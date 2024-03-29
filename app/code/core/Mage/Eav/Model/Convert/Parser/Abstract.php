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


abstract class Mage_Eav_Model_Convert_Parser_Abstract extends Varien_Convert_Parser_Abstract
{
    protected $_storesById;
    protected $_attributeSetsById;
    protected $_attributeSetsByName;

    public function getStoreIds($stores)
    {
       if (empty($stores)) {
            $storeIds = array(0);
        } else {
            $storeIds = array();
            foreach (explode(',', $stores) as $store) {
                if (is_numeric($store)) {
                    $storeIds[] = $store;
                } else {
                    $storeNode = Mage::getConfig()->getNode('stores/'.$store);
                    if (!$storeNode) {
                        return false;
                    }
                    $storeIds[] = (int)$storeNode->system->store->id;
                }
            }
        }
        return $storeIds;
    }

    public function getStoreCode($storeId)
    {
        if (!$this->_storesById) {
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
                $this->_storesById[(int)$storeNode->system->store->id] = $storeNode->getName();
            }
        }
        if (is_numeric($storeId)) {
            return isset($this->_storesById[$storeId]) ? $this->_storesById[$storeId] : false;
        } else {
            return array_search($storeId, $this->_storesById)!==false ? $storeId : false;
        }
    }

    public function loadAttributeSets($entityTypeId)
    {
        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->load();
        $this->_attributeSetsById = array();
        $this->_attributeSetsByName = array();
        foreach ($attributeSetCollection as $id=>$attributeSet) {
            $name = $attributeSet->getAttributeSetName();
            $this->_attributeSetsById[$id] = $name;
            $this->_attributeSetsByName[$name] = $id;
        }
        return $this;
    }

    public function getAttributeSetName($entityTypeId, $id)
    {
        if (!$this->_attributeSetsById) {
            $this->loadAttributeSets($entityTypeId);
        }
        return isset($this->_attributeSetsById[$id]) ? $this->_attributeSetsById[$id] : false;
    }

    public function getAttributeSetId($entityTypeId, $name)
    {
        if (!$this->_attributeSetsByName) {
            $this->loadAttributeSets($entityTypeId);
        }
        return isset($this->_attributeSetsByName[$name]) ? $this->_attributeSetsByName[$name] : false;
    }

    public function getSourceOptionId(Mage_Eav_Model_Entity_Attribute_Source_Interface $source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value)==0) {
                return $option['value'];
            }
        }
        return null;
    }
}