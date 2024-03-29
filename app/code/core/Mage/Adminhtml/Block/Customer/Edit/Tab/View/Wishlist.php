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
 * Adminhtml customer view wishlist block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Customer_Edit_Tab_View_Wishlist extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_view_wishlist_grid');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setEmptyText(Mage::helper('customer')->__("There are no items in customer's wishlist at the moment"));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('wishlist/wishlist')->loadByCustomer(Mage::registry('current_customer'))
            ->getProductCollection()
                ->addAttributeToSelect('name')
                ->addStoreData();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->getParentBlock()->setTitle(Mage::helper('customer')->__('Wishlist - %d item(s)', $this->getCollection()->getSize()));
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('customer')->__('Product ID'),
            'index'     => 'product_id',
            'type'      => 'number',
            'width'     => '100px'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('customer')->__('Product Name'),
            'index'     => 'name'
        ));

        $stores = Mage::getResourceModel('core/store_collection')
            ->setWithoutDefaultFilter()
            ->load()
                ->toOptionHash();

        $this->addColumn('store', array(
            'header'    => Mage::helper('customer')->__('Added From'),
            'index'     => 'store_id',
            'type' => 'options',
            'options' => $stores,
            'width'     => '160px',
        ));

        $this->addColumn('added_at', array(
            'header'    => Mage::helper('customer')->__('Date Added'),
            'index'     => 'added_at',
            'type'      => 'date',
            'width'     => '140px',
        ));

        $this->addColumn('days', array(
            'header'    => Mage::helper('customer')->__('Days in Wishlist'),
            'index'     => 'days_in_wishlist',
            'type'      => 'number',
            'width'     => '140px',
        ));

        return parent::_prepareColumns();
    }

    public function getHeadersVisibility()
    {
        return ($this->getCollection()->getSize() > 0);
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }
}
