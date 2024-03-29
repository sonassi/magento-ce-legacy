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
 * Adminhtml tags by products report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Report_Tag_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('gridProducts');
    }

    protected function _prepareCollection()
    {

        $collection = Mage::getResourceModel('reports/tag_product_collection');

        $collection->addUniqueTagedCount()
            ->addAllTagedCount()
            ->addStatusFilter(Mage::getModel('tag/tag')->getApprovedStatus())
            ->addGroupByProduct();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('entity_id', array(
            'header'    =>Mage::helper('reports')->__('ID'),
            'width'     =>'50px',
            'align'     =>'right',
            'index'     =>'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'index'     =>'name'
        ));

        $this->addColumn('utaged', array(
            'header'    =>Mage::helper('reports')->__('Number of Unique Tags'),
            'width'     =>'50px',
            'align'     =>'right',
            'index'     =>'utaged'
        ));

        $this->addColumn('taged', array(
            'header'    =>Mage::helper('reports')->__('Number of Total Tags'),
            'width'     =>'50px',
            'align'     =>'right',
            'index'     =>'taged'
        ));

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportProductCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportProductXml', Mage::helper('reports')->__('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/productDetail', array('id'=>$row->entity_id));
    }

}
