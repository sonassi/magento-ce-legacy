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
 * Adminhtml customer orders grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Cart extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_cart_grid');
        $this->setUseAjax(true);
        $this->_parentTemplate = $this->getTemplateName();
        $this->setTemplate('customer/tab/cart.phtml');
    }
    
    protected function _prepareCollection()
    {
        $quote = Mage::getResourceModel('sales/quote_collection')
            ->loadByCustomerId(Mage::registry('current_customer')->getId());

        if ($quote) {
            $collection = $quote->getItemsCollection(false);
        }
        else {
            $collection = new Varien_Data_Collection();
        }

        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('customer')->__('Product ID'),
            'index' => 'product_id',
            'width' => '100px',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('customer')->__('Product Name'),
            'index' => 'name',
        ));
        
        $this->addColumn('sku', array(
            'header' => Mage::helper('customer')->__('SKU'),
            'index' => 'sku',
            'width' => '100px',
        ));
        
        $this->addColumn('qty', array(
            'header' => Mage::helper('customer')->__('Qty'),
            'index' => 'qty',
            'type'  => 'number',
            'width' => '60px',
        ));
        
        $this->addColumn('price', array(
            'header' => Mage::helper('customer')->__('Price'),
            'index' => 'price',
            'type'  => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));
        
        $this->addColumn('total', array(
            'header' => Mage::helper('customer')->__('Total'),
            'index' => 'row_total',
            'type'  => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('customer')->__('Action'),
            'index'     => 'quote_item_id',
            'type'      => 'action',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption' =>  Mage::helper('customer')->__('Delete'),
                    'url'     =>  '#',
                    'onclick' =>  'return cartControl.removeItem($entity_id);'
                )
            )
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return Mage::getUrl('*/*/cart', array('_current'=>true));
    }
    
    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    public function getRowUrl($row)
    {
        return Mage::getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }    
}
