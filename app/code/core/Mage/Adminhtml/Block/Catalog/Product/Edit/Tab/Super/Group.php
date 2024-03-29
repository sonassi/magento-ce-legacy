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
 * Product in category grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group extends Mage_Adminhtml_Block_Widget_Grid 
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId('super_product_grid');
        $this->setDefaultFilter(array('in_products'=>1));
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            else {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        
        return $this;
    }
    
    protected function _prepareCollection()
    {
       
        $collection = Mage::getResourceModel('catalog/product_link_collection')
            ->setLinkType('super')
            ->setProductId(Mage::registry('product')->getId())
            ->setStoreId(Mage::registry('product')->getStoreId())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('type_id', 1) // Todo: remove harcoded value
            ->addLinkAttributeToSelect('position')
            ->addLinkAttributeToSelect('qty')
            ->useProductItem();

        $this->setCollection($collection);
        
        

        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));
        
        $this->addColumn('id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'      => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));
        
        $this->addColumn('qty', array(
            'header'    => Mage::helper('catalog')->__('Default Qty'),
            'name'      => 'qty',            
            'align'     => 'center',
            'type'      => 'number',
            'validate_class' => 'validate-number',
            'index'     => 'qty',
            'width'     => '60px',
            'editable'  => true
        ));
        
        $this->addColumn('position', array(
            'header'    => Mage::helper('catalog')->__('Position'),
            'name'      => 'position',
            'align'     => 'center',
            'type'      => 'number',
            'validate_class' => 'validate-number',
            'index'     => 'position',
            'width'     => '60px',
            'editable'  => true
        ));         
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return Mage::getUrl('*/*/superGroup', array('_current'=>true));
    }
    
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', null);
        
        if (!is_array($products)) {
            $products = Mage::registry('product')->getSuperGroupProductsLoaded()->getColumnValues('entity_id');
        }
        
        return $products;
    }
}