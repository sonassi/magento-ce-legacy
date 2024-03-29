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
 * Child Of Mage_Adminhtml_Block_Tag_Customer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tag_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_grid' . Mage::registry('tagId'));
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $tagId = Mage::registry('tagId');
        $collection = Mage::getModel('tag/tag')
            ->getCustomerCollection()
            ->addTagFilter($tagId)
            ->setCountAttribute('tr.tag_relation_id')
            ->addGroupByCustomerProduct();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->addProductName();
        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', array(
            'header'        => Mage::helper('tag')->__('ID'),
            'width'         => '50px',
            'align'         => 'right',
            'index'         => 'entity_id',
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('tag')->__('First Name'),
            'index'     => 'firstname',
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('tag')->__('Last Name'),
            'index'     => 'lastname',
        ));

        $stores = Mage::getResourceModel('core/store_collection')->setWithoutDefaultFilter()->load()->toOptionHash();

        $this->addColumn('store_id', array(
            'header' => Mage::helper('tag')->__('Tagged in'),
            'index' => 'store_id',
            'type' => 'options',
            'options' => $stores,
        ));

        $this->addColumn('product', array(
            'header'    => Mage::helper('tag')->__('Product Name'),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'product',
        ));

        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('tag')->__('Product SKU'),
            'filter'    => false,
            'sortable'  => false,
            'width'     => '50px',
            'align'     => 'right',
            'index'     => 'product_sku',
        ));

        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('tag')->__('Product SKU'),
            'filter'    => false,
            'sortable'  => false,
            'width'     => '50px',
            'align'     => 'right',
            'index'     => 'product_sku',
        ));

        return parent::_prepareColumns();
    }

    protected function getRowUrl($row)
    {
        return Mage::getUrl('*/customer/edit', array('id' => $row->getCustomerId()));
    }
}