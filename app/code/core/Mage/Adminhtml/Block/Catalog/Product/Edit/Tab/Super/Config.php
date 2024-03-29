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
 * Adminhtml catalog super product configurable tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/edit/super/config.phtml');
        $this->setId('config_super_product');
    }

    public function getAttributesJson()
    {
        $attributes = Mage::registry('product')->getSuperAttributes();
        $this->_clearDeletedValues($attributes);
        if(!$attributes) {
            return '[]';
        }
        return Zend_Json::encode($attributes);
    }

    /**
     * Clears deleted products link in attributes from configured product
     *
     * @param array $attributes
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
     */
    protected function _clearDeletedValues(&$attributes)
    {
        $links = Mage::registry('product')->getSuperLinks();
        if(!$links) {
            $links = array();
        }

        $existsIndicator = array();
        foreach($links as &$link) {
            foreach ($link as &$linkAttribute) {
                if(!isset($existsIndicator[$linkAttribute['attribute_id']])) {
                    $existsIndicator[$linkAttribute['attribute_id']] = array();
                }
                $existsIndicator[$linkAttribute['attribute_id']][$linkAttribute['value_index']] = 1;
            }
        }

        foreach($attributes as &$attribute) {
            foreach ($attribute['values'] as $valueKey=>&$value) {
                if(!isset($existsIndicator[$attribute['attribute_id']][$value['value_index']])) {
                    unset($attribute['values'][$valueKey]);
                }
            }
            $attribute['values'] = array_values($attribute['values']);
        }

        unset($existsIndicator);
        return $this;
    }

    public function getLinksJson()
    {
        $links = Mage::registry('product')->getSuperLinks();
        if(!$links) {
            return '{}';
        }
        return Zend_Json::encode($links);
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid',
            $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_super_config_grid')
        );
        return parent::_prepareLayout();
    }

    protected function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    protected function getGridJsObject()
    {
        return $this->getChild('grid')->getJsObjectName();
    }

}// Class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config END