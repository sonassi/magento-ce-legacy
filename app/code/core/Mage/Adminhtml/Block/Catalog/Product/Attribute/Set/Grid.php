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

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('setGrid');
        $this->setDefaultSort('set_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::registry('entityType'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /*$this->addColumn('set_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'align'     => 'right',
            'sortable'  => true,
            'width'     => '50px',
            'index'     => 'attribute_set_id',
        ));*/

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Set Name'),
            'align'     => 'left',
            'sortable'  => true,
            'index'     => 'attribute_set_name',
        ));
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/edit', array('id'=>$row->getAttributeSetId()));
    }
}