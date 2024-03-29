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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Eav_Model_Entity_Attribute_Source_Table extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty=true)
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttribute()->getId())
                ->setStoreFilter($this->getAttribute()->getEntity()->getStoreId())
                ->setOrder('value', 'asc')
                ->load()
                ->toOptionArray();
            if ($withEmpty) {
                array_unshift($this->_options, array('label'=>'', 'value'=>''));
            }
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($this->getAttribute()->getId())
            ->setStoreFilter($this->getAttribute()->getEntity()->getStoreId())
            ->setIdFilter($value)
            ->load();
        if ($isMultiple) {
            $values = array();
            foreach ($collection as $item) {
            	$values[] = $item->getValue();
            }
            return $values;
        }
        else {
            if ($item = $collection->getFirstItem()) {
                return $item->getValue();
            }
            return false;
        }
    }
}