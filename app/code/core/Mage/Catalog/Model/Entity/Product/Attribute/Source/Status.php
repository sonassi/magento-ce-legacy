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
 * Product status attribute source model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Product_Attribute_Source_Status extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($addEmptyOption = true)
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('catalog/product_status_collection')
                //->setOrder('value', 'asc')
                ->load()
                ->toOptionArray();
                
            if ($addEmptyOption) {
                array_unshift($this->_options, array('label'=>'', 'value'=>''));
            }
        }
        return $this->_options;
    }
}
