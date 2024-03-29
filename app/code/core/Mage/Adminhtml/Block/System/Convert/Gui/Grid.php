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
 * Convert profiles grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Convert_Gui_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('convertProfileGrid');
        $this->setDefaultSort('id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('core/convert_profile_collection')
            ->addFieldToFilter('entity_type', array('neq'=>''));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    =>Mage::helper('adminhtml')->__('ID'),
            'width'     =>'50px',
            'index'     =>'profile_id',
        ));
        $this->addColumn('name', array(
            'header'    =>Mage::helper('adminhtml')->__('Profile Name'),
            'index'     =>'name',
        ));
        $this->addColumn('direction', array(
            'header'    =>Mage::helper('adminhtml')->__('Profile Direction'),
            'index'     =>'direction',
            'type'      =>'options',
            'options'   =>array('import'=>'Import', 'export'=>'Export'),
            'width'     =>'120px',
        ));
        $this->addColumn('entity_type', array(
            'header'    =>Mage::helper('adminhtml')->__('Entity Type'),
            'index'     =>'entity_type',
            'type'      =>'options',
            'options'   =>array('product'=>'Products', 'customer'=>'Customers'),
            'width'     =>'120px',
        ));
        $stores = Mage::getResourceModel('core/store_collection')->setLoadDefault(true)->load()->toOptionHash();
        $stores[0] = $this->__('Default Values');
        $this->addColumn('store_id', array(
            'header'    =>Mage::helper('adminhtml')->__('Store'),
            'type'      => 'options',
            'align'     => 'center',
            'index'     => 'store_id',
            'options'   => $stores,
            'width'     => '120px',
        ));
        $this->addColumn('created_at', array(
            'header'    =>Mage::helper('adminhtml')->__('Created At'),
            'type'      => 'date',
            'align'     => 'center',
            'index'     =>'created_at',
        ));
        $this->addColumn('updated_at', array(
            'header'    =>Mage::helper('adminhtml')->__('Updated At'),
            'type'      => 'date',
            'align'     => 'center',
            'index'     =>'updated_at',
        ));

        $this->addColumn('action', array(
            'header'    =>Mage::helper('adminhtml')->__('Action'),
            'width'     =>'60px',
            'sortable'  =>false,
            'filter'    => false,
            'type' => 'action',
            'actions' => array(
                array(
                    'url' => Mage::getUrl('*/*/run').'id/$profile_id',
                    'caption' => Mage::helper('adminhtml')->__('Run in popup'),
                    'target' => '_blank',
                ),
            )
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
