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
 * Adminhtml customers groups grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Customer_Group_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGroupGrid');
        $this->setDefaultSort('type');
        $this->setDefaultDir('asc');
    }

    /**
     * Init customer groups collection
     * @return void
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/group_collection')
        	->addFieldToFilter('customer_group_id', array('neq'=>0))
            ->addTaxClass();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('time', array(
            'header' => Mage::helper('customer')->__('ID'),
            'width' => '50px',
            'align' => 'right',
            'index' => 'customer_group_id',
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('customer')->__('Group Name'),
            'index' => 'customer_group_code',
        ));

        $this->addColumn('class_name', array(
            'header' => Mage::helper('customer')->__('Tax Class'),
            'index' => 'class_name',
            'width' => '200px'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}