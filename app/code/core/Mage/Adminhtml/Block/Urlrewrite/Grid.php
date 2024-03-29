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
 * Adminhtml urlrewrite grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Urlrewrite_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('urlrewriteGrid');
        $this->setDefaultSort('id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('core/url_rewrite_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => $this->__('ID'),
            'width'     => '50px',
            'index'     => 'url_rewrite_id'
        ));

        $stores = Mage::getResourceModel('core/store_collection')->setWithoutDefaultFilter()->load()->toOptionHash();

        $this->addColumn('store_id', array(
            'header'    => $this->__('Store View'),
            'width'     => '50px',
            'index'     => 'store_id',
            'type'      => 'options',
            'options'   => $stores
        ));

        $this->addColumn('type', array(
            'header'    =>$this->__('Type'),
            'width'     => '50px',
            'index'     => 'type',
            'type'      => 'options',
            'options'   => array(
                1 => $this->__('Category'),
                2 => $this->__('Product'),
                3 => $this->__('Custom')
            ),
        ));

        $this->addColumn('id_path', array(
            'header'    => $this->__('ID Path'),
            'width'     => '50px',
            'index'     => 'id_path'
        ));
        $this->addColumn('request_path', array(
            'header'    => $this->__('Request Path'),
            'width'     => '50px',
            'index'     => 'request_path'
        ));
        $this->addColumn('target_path', array(
            'header'    => $this->__('Target Path'),
            'width'     => '50px',
            'index'     => 'target_path'
        ));
        $this->addColumn('options', array(
            'header'    => $this->__('Options'),
            'width'     => '50px',
            'index'     => 'options'
        ));
        $this->addColumn('actions', array(
            'header'    => $this->__('Action'),
            'width'     => '15px',
            'sortable'  => false,
            'filter'    => false,
            'type'      => 'action',
            'actions'   => array(
                array(
                    'url'       => Mage::getUrl('*/*/edit') . 'id/$url_rewrite_id',
                    'caption'   => $this->__('Edit'),
                ),
            )
        ));
        //$this->addExportType('*/*/exportCsv', $this->__('CSV'));
        //$this->addExportType('*/*/exportXml', $this->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        //return Mage::getUrl('*/*/edit', array('id'=>$row->getId()));
        //return Mage::getUrl('*/*/view', array('id' => $row->getId()));
    }
}
