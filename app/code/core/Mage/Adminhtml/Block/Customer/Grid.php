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
class Mage_Adminhtml_Block_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
    }



    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_regione', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinField('store_name', 'core/store', 'name', 'store_id=store_id', null, 'left');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    =>Mage::helper('customer')->__('ID'),
            'width'     =>'50px',
            'index'     =>'entity_id'
        ));
        $this->addColumn('firstname', array(
            'header'    =>Mage::helper('customer')->__('First Name'),
            'index'     =>'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    =>Mage::helper('customer')->__('Last Name'),
            'index'     =>'lastname'
        ));
        $this->addColumn('email', array(
            'header'    =>Mage::helper('customer')->__('Email'),
            'width'     =>'150',
            'index'     =>'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=>0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>Mage::helper('customer')->__('Group'),
            'width'     =>'100',
            'index'     =>'group_id',
            'type'  => 'options',
            'options' => $groups,
        ));
        $this->addColumn('Telephone', array(
            'header'    =>Mage::helper('customer')->__('Telephone'),
            'width'     =>'100',
            'index'     =>'billing_telephone'
        ));
        $this->addColumn('billing_postcode', array(
            'header'    =>Mage::helper('customer')->__('ZIP/Postal Code'),
            'width'     =>'90',
            'index'     =>'billing_postcode',
        ));
        $this->addColumn('billing_country_id', array(
            'header'    =>Mage::helper('customer')->__('Country'),
            'width'     =>'100',
            'type'      =>'country',
            'index'     =>'billing_country_id',
        ));
        $this->addColumn('billing_regione', array(
            'header'    =>Mage::helper('customer')->__('State/Province'),
            'width'     =>'100',
            #'filter'    => 'adminhtml/customer_grid_filter_country',
            'index'     =>'billing_regione',
        ));
        $this->addColumn('customer_since', array(
            'header'    =>Mage::helper('customer')->__('Customer Since'),
            'type'      => 'date',
            'align'     => 'center',
            'index'     =>'created_at',
        ));
        $this->addColumn('store_name', array(
            'header'    =>Mage::helper('customer')->__('Signed Up From'),
            'align'     => 'center',
            'width'     => '80px',
            'index'     =>'store_name',
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('customer')->__('Edit'),
                        'url'     => array('base'=>'*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('customer')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('customer')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('newsletter_subscribe', array(
             'label'=> Mage::helper('customer')->__('Subscribe to newsletter'),
             'url'  => $this->getUrl('*/*/massSubscribe')
        ));

        $this->getMassactionBlock()->addItem('newsletter_unsubscribe', array(
             'label'=> Mage::helper('customer')->__('Unsubscribe from newsletter'),
             'url'  => $this->getUrl('*/*/massUnsubscribe')
        ));

        $groups = $this->helper('customer')->getGroups()->toOptionArray();

        array_unshift($groups, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('assign_group', array(
             'label'=> Mage::helper('customer')->__('Assign a customer group'),
             'url'  => $this->getUrl('*/*/massAssignGroup'),
             'additional' => array(
                    'visibility' => array(
                             'name' => 'group',
                             'type' => 'select',
                             'class' => 'required-entry',
                             'label' => Mage::helper('customer')->__('Group'),
                             'values' => $groups
                         )
             )
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
