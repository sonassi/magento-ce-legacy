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
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * List of products tagged by customer
 *
 * @category   Mage
 * @package    Mage_Tag
 */

class Mage_Tag_Block_Customer_View extends Mage_Core_Block_Template
{
    protected $_collection;

    protected $_tagInfo;

    public function __construct()
    {
        $this->setTemplate('tag/customer/view.phtml');
        $this->setTagId(Mage::registry('tagId'));
    }

    public function getTagInfo()
    {
        if( !$this->_tagInfo ) {
            $this->_tagInfo = Mage::getModel('tag/tag')->load($this->getTagId());
        }
        return $this->_tagInfo;
    }

    public function getMyProducts()
    {
        return $this->_getCollection()->getItems();
    }

    public function getCount()
    {
        return sizeof($this->getMyProducts());
    }

    public function getReviewUrl($productId)
    {
        return Mage::getUrl('review/product/list', array('id' => $productId));
    }

    protected function _prepareLayout()
    {
        $toolbar = $this->getLayout()->createBlock('page/html_pager', 'customer_tag_list.toolbar')
            ->setCollection($this->_getCollection());

        $this->setChild('toolbar', $toolbar);
        return parent::_prepareLayout();
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }

    protected function _getCollection()
    {
        if( !$this->_collection ) {
            $tagModel = Mage::getModel('tag/tag');
            $this->_collection = $tagModel->getEntityCollection();

            $this->_collection
                ->addTagFilter($this->getTagId())
                ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setActiveFilter()
                ->addAttributeToSelect('description');
        }
        return $this->_collection;
    }

    protected function _beforeToHtml()
    {
        $this->_getCollection()->load();
        Mage::getModel('review/review')->appendSummary($this->_getCollection());
        return parent::_beforeToHtml();
    }
}