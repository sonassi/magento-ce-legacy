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
 * Adminhtml product attribute edit page tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Attribute Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main', array(
            'label'     => Mage::helper('catalog')->__('Attribute Properties'),
            'title'     => Mage::helper('catalog')->__('Attribute Properties'),
            'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('front', array(
            'label'     => Mage::helper('catalog')->__('Frontend Properties'),
            'title'     => Mage::helper('catalog')->__('Frontend Properties'),
            'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit_tab_front')->toHtml(),
        ));

        $this->addTab('system', array(
            'label'     => Mage::helper('catalog')->__('System Properties'),
            'title'     => Mage::helper('catalog')->__('System Properties'),
            'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit_tab_system')->toHtml(),
        ));

        $model = Mage::registry('entity_attribute');

        $this->addTab('labels', array(
            'label'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'title'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit_tab_options')->toHtml(),
        ));
        
        /*if ('select' == $model->getFrontendInput()) {
            $this->addTab('options_section', array(
                'label'     => Mage::helper('catalog')->__('Options Control'),
                'title'     => Mage::helper('catalog')->__('Options Control'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit_tab_options')->toHtml(),
            ));
        }*/

        return parent::_beforeToHtml();
    }

}
