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
 * Adminhtml pending tags grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tag_Grid_Pending extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pending_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('tag/tag_collection')
            ->addSummary(0)
            ->addStoresVisibility()
            ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_PENDING);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = Mage::getUrl();

        $this->addColumn('name', array(
            'header'    => Mage::helper('tag')->__('Tag'),
            'index'     => 'name',
        ));

        $this->addColumn('total_used', array(
            'header'    => Mage::helper('tag')->__('Uses'),
            'width'     => '140px',
            'align'     => 'right',
            'index'     => 'uses',
            'type'      => 'number',
        ));

        $this->addColumn('products', array(
            'header'    => Mage::helper('tag')->__('Products'),
            'width'     => '140px',
            'align'     => 'right',
            'index'     => 'products',
            'type'      => 'number',
        ));

        $this->addColumn('customers', array(
            'header'    => Mage::helper('tag')->__('Customers'),
            'width'     => '140px',
            'align'     => 'right',
            'index'     => 'customers',
            'type'      => 'number',
        ));

        $this->addColumn('popularity', array(
            'header'    => Mage::helper('tag')->__('Popularity'),
            'width'     => '140px',
            'align'     => 'right',
            'index'     => 'popularity',
            'type'      => 'number',
        ));


        /*
        $this->addColumn('status', array(
            'header'    => Mage::helper('tag')->__('Status'),
            'width'     => '90px',
            'index'     => 'status',
            'type'      => 'options',
            'filter'    => false,
            'sortable'  => false,
            'options'    => array(
                Mage_Tag_Model_Tag::STATUS_DISABLED => Mage::helper('tag')->__('Disabled'),
                Mage_Tag_Model_Tag::STATUS_PENDING  => Mage::helper('tag')->__('Pending'),
                Mage_Tag_Model_Tag::STATUS_APPROVED => Mage::helper('tag')->__('Approved'),
            ),
        ));
        */

          // Collection for stores filters
        if(!$collection = Mage::registry('stores_select_collection')) {
            $collection =  Mage::app()->getStore()->getResourceCollection()
                ->load();
            Mage::register('stores_select_collection', $collection);
        }

         $this->addColumn('visible_in', array(
            'header'    => Mage::helper('tag')->__('Visible In'),
            'type'      => 'select',
            'index'     => 'stores',
             'sortable'  => false,
            'filter'      => 'adminhtml/tag_grid_all_filter_visible',
            'renderer'      => 'adminhtml/tag_grid_all_renderer_visible'
        ));


        $this->addColumn('actions', array(
            'header'    => Mage::helper('tag')->__('Actions'),
            'width'     => '100px',
            'type'      => 'action',
            'sortable'  => false,
            'filter'    => false,
            'actions'    => array(
                array(
                    'caption'   => Mage::helper('tag')->__('Edit Tag'),
                    'url'       => Mage::getUrl('*/*/edit', array('ret' => 'pending', 'tag_id'=>'$tag_id')),
                ),
                array(
                    'caption'   => Mage::helper('tag')->__('View Products'),
                    'url'       => Mage::getUrl('*/*/product', array('ret' => 'pending', 'tag_id'=>'$tag_id')),
                ),

                array(
                    'caption'   => Mage::helper('tag')->__('View Customers'),
                    'url'       => Mage::getUrl('*/*/customer', array('ret' => 'pending', 'tag_id'=>'$tag_id')),
                )
            ),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/edit', array(
            'tag_id' => $row->getId(),
            'ret'    => 'pending',
        ));
    }

    protected function _addColumnFilterToCollection($column)
    {
         if($column->getIndex()=='stores') {
                $this->getCollection()->addStoreFilter($column->getFilter()->getCondition(), false);
         } else {
                parent::_addColumnFilterToCollection($column);
         }

         return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('tag');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('tag')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete', array('ret' => 'pending')),
             'confirm' => Mage::helper('tag')->__('Are you sure?')
        ));

        $statuses = $this->helper('tag/data')->getStatusesOptionsArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));

        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('tag')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true, 'ret' => 'pending')),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('tag')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        return $this;
    }
}