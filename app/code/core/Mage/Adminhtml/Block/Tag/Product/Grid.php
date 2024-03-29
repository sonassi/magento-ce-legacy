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
 * Child Of Mage_Adminhtml_Block_Tag_Product
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tag_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
            ->getEntityCollection()
            ->addTagFilter($tagId)
            ->addPopularity($tagId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'        => Mage::helper('tag')->__('ID'),
            'width'         => '50px',
            'align'         => 'right',
            'index'         => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('tag')->__('Product Name'),
            'index'     => 'name',
        ));

        $this->addColumn('popularity', array(
            'header'        => Mage::helper('tag')->__('# of Uses'),
            'width'         => '50px',
            'align'         => 'right',
            'index'         => 'popularity',
            'type'          => 'number'
        ));

        return parent::_prepareColumns();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if($column->getIndex() == 'popularity') {
            $this->getCollection()->addPopularityFilter($column->getFilter()->getCondition());
            return $this;
        } else {
            return parent::_addColumnFilterToCollection($column);
        }
    }

    protected function getRowUrl($row)
    {
        return Mage::getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }
}