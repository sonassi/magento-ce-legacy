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
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Customer_Online_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('onlineGrid');
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('last_activity');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
    	parent::_prepareCollection();
    	foreach ($this->getCollection()->getItems() as $item) {
        	$item->addIpData($item)
                 ->addCustomerData($item)
        	     ->addQuoteData($item);
        }
        return $this;
    }

    protected function _initCollection()
    {
        $collection = Mage::getResourceSingleton('log/visitor_collection')
            ->useOnlineFilter();

        $this->setCollection($collection);
    }

    protected function _beforeToHtml()
    {
        $this->addColumn('id', array(
                            'header'=>Mage::helper('customer')->__('ID'),
                            'width'=>'40px',
                            'align'=>'right',
                            'filter' => false,
                            'sortable' => false,
                            'default' => Mage::helper('customer')->__('n/a'),
                            'index'=>'customer_id')
                        );

        $this->addColumn('firstname', array(
                            'header'=> Mage::helper('customer')->__('First Name'),
                            'filter' => false,
                            'sortable' => false,
                            'default' => Mage::helper('customer')->__('Guest'),
                            'index'=>'customer_firstname')
                        );

        $this->addColumn('lastname', array(
                            'header'=> Mage::helper('customer')->__('Last Name'),
                            'filter' => false,
                            'sortable' => false,
                            'default' => Mage::helper('customer')->__('n/a'),
                            'index'=>'customer_lastname')
                        );

        $this->addColumn('email', array(
                            'header'=> Mage::helper('customer')->__('Email'),
                            'filter' => false,
                            'sortable' => false,
                            'default' => Mage::helper('customer')->__('n/a'),
                            'index'=>'customer_email')
                        );

        $this->addColumn('ip_address', array(
                            'header'=> Mage::helper('customer')->__('IP Address'),
                            'index'=>'remote_addr',
                            'default' => Mage::helper('customer')->__('n/a'),
        	                'renderer'=>'adminhtml/customer_online_grid_renderer_ip')
        	            );

        $this->addColumn('session_start_time', array(
                            'header'=> Mage::helper('customer')->__('Session Start Time'),
                            'align'=>'left',
                            'type' => 'datetime',
                            'default' => Mage::helper('customer')->__('n/a'),
                            'width' => '200px',
                            'index'=>'first_visit_at')
                        );

        $this->addColumn('last_activity', array(
                            'header'=> Mage::helper('customer')->__('Last Activity'),
                            'align'=>'left',
                            'type' => 'datetime',
                            'default' => Mage::helper('customer')->__('n/a'),
                            'width' => '200px',
                            'index'=>'last_visit_at')
                        );

        $this->addColumn('type', array(
                            'header'=> Mage::helper('customer')->__('Type'),
                            'index'=>'customer_id',
                            'type' => 'options',
                            'options' => array(
                                'c' => Mage::helper('customer')->__('Customer'),
                                'v' => Mage::helper('customer')->__('Visitor'),
                            ),
                            'renderer'=>'adminhtml/customer_online_grid_renderer_type',
                        ));

        $this->addColumn('last_url', array(
                            'header'=> Mage::helper('customer')->__('Last Url'),
                            'default' => Mage::helper('customer')->__('n/a'),
                            'index'=>'url')
                        );

        $this->_initCollection();
        return parent::_beforeToHtml();
    }

    public function getRowUrl($row)
    {
        return ( intval($row->getCustomerId()) > 0 ) ? Mage::getUrl('*/customer/edit', array('id' => $row->getCustomerId())) : '#';
    }

    protected function _addColumnFilterToCollection($column)
    {
        $this->getCollection()->addFieldToFilter($column->getId(), $column->getFilter()->getValue());
        return $this;
    }
}
