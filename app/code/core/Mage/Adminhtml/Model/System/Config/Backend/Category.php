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
 * Config category field backend
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Model_System_Config_Backend_Category
{
    public function afterSave(Varien_Object $configData)
    {
        if ($configData->getScope() == 'stores') {
            $rootId     = $configData->getValue();
            $oldRootId  = $configData->getOldValue();
            $storeId    = $configData->getScopeId();

            $category   = Mage::getSingleton('catalog/category');
            $tree       = $category->getTreeModel();

            // Create copy of categories attributes for choosed store
            $tree->load();
            $root = $tree->getNodeById($rootId);

            // Save root
            $category->setStoreId(0)
        	   ->load($root->getId());
            $category->setStoreId($storeId)
                ->save();

            foreach ($root->getAllChildNodes() as $node) {
            	$category->setStoreId(0)
            	   ->load($node->getId());
                $category->setStoreId($storeId)
                    ->save();
            }
        }
        return $configData;
    }
}
