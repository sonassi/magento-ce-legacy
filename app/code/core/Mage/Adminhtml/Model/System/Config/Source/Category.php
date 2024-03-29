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
 * Config category source
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Model_System_Config_Source_Category
{
    public function toOptionArray()
    {
        $tree = Mage::getResourceModel('catalog/category_tree');
        
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->getEntity()
            ->setStore(0);
        $collection->addAttributeToSelect('name')
            ->addFieldToFilter('parent_id', 1)
            ->load();

        $options = array();
        
        $options[] = array(
            'label' => '',
            'value' => ''
        );
        foreach ($collection as $category) {
            $options[] = array(
               'label' => $category->getName(),
               'value' => $category->getId()
            );
        }
        
        return $options;
    }
}
