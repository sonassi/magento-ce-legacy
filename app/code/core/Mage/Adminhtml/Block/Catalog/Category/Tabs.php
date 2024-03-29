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
 * Category tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Category_Tabs extends Mage_Adminhtml_Block_Widget_Tabs 
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId('category_info_tabs');
        $this->setDestElementId('category_tab_content');
        $this->setTitle(Mage::helper('catalog')->__('Category Data'));
                $this->setTemplate('widget/tabshoriz.phtml');
        
        //$this->setDestElementId('category_tab_content');
    }
    
    protected function _prepareLayout()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('catalog')->__('General Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/catalog_category_tab_general')->toHtml(),
            'active'    => true
        ));

        $this->addTab('products', array(
            'label'     => Mage::helper('catalog')->__('Category Products'),
            'content'   => $this->getLayout()->createBlock('adminhtml/catalog_category_tab_product', 'category.product.grid')->toHtml(),
        ));

        $this->addTab('design', array(
            'label'     => Mage::helper('catalog')->__('Custom Design'),
            'content'   => $this->getLayout()->createBlock('adminhtml/catalog_category_tab_design')->toHtml(),
        ));

        /*$this->addTab('features', array(
            'label'     => Mage::helper('catalog')->__('Feature Products'),
            'content'   => 'Feature Products'
        ));        */
        return parent::_prepareLayout();
    }
}
