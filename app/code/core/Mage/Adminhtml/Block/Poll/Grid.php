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
 * description
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Poll_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pollGrid');
        $this->setDefaultSort('poll_title');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('poll/poll')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        $this->getCollection()->addStoreData();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('poll_id', array(
            'header'    => Mage::helper('poll')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'poll_id',
        ));

        $this->addColumn('poll_title', array(
            'header'    => Mage::helper('poll')->__('Poll Question'),
            'align'     =>'left',
            'index'     => 'poll_title',
        ));

        $this->addColumn('votes_count', array(
            'header'    => Mage::helper('poll')->__('Number of Responses'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'votes_count',
        ));

        $this->addColumn('date_posted', array(
            'header'    => Mage::helper('poll')->__('Date Posted'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'index'     => 'date_posted',
        ));

        $this->addColumn('date_closed', array(
            'header'    => Mage::helper('poll')->__('Date Closed'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'date_closed',
        ));

        $this->addColumn('visible_in', array(
            'header'    => Mage::helper('review')->__('Visible In'),
            'type'      => 'select',
            'index'     => 'stores',
            'filter'    => 'adminhtml/poll_grid_filter_store',
            'renderer'  => 'adminhtml/poll_grid_renderer_store'
        ));

        /*
        $this->addColumn('active', array(
            'header'    => Mage::helper('poll')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));
        */
        $this->addColumn('closed', array(
            'header'    => Mage::helper('poll')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'closed',
            'type'      => 'options',
            'options'   => array(
                1 => 'Closed',
                0 => 'Open',
            ),
        ));


        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/edit', array('id' => $row->getId()));
    }
}