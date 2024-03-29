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


class Mage_Adminhtml_Model_System_Config_Source_Catalog_ProductPerPage
{
    public function toOptionArray($listMode = null)
    {
        if ($listMode == null) {
            // current list mode
            $listMode = Mage::getStoreConfig('catalog/frontend/list_mode');
        }

        if ($listMode == 'list' || $listMode == 'list-grid') {
            $perPageValues = Mage::getConfig()->getNode('frontend/catalog/per_page_values/list');
        }
        elseif ($listMode == 'grid' || $listMode == 'grid-list') {
            $perPageValues = Mage::getConfig()->getNode('frontend/catalog/per_page_values/grid');
        }

        $perPageValues = explode(',', $perPageValues);
        foreach ($perPageValues as $option) {
            $result[] = array('value' => $option, 'label' => $option);
        }

        if ($listMode == 'list' || $listMode == 'grid') {
            $result[] = array('value' => 'all', 'label' => Mage::helper('catalog')->__('All'));
        }
        return $result;
    }
}