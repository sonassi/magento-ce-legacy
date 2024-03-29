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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog comapare controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
 class Mage_Catalog_Product_CompareController extends Mage_Core_Controller_Front_Action
 {
    public function indexAction()
    {
        $items = $this->getRequest()->getParam('items');

        if($this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
            Mage::getSingleton('catalog/session')->setBeforeCompareUrl(
                base64_decode($this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL))
            );
        }

        if ($items) {
            $items = explode(',', $items);
            $list = Mage::getSingleton('catalog/product_compare_list');
            $list->addProducts($items);
            $this->_redirect('*/*/*');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Add item to compare list
     */
    public function addAction()
    {
        $productId = (int)$this->getRequest()->getParam('product');

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);

        if($product->getId()) {
            Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
            Mage::getSingleton('catalog/session')
                        ->addSuccess(Mage::helper('catalog')->__('Product %s successfully added to compare list', $product->getName()));
        }

        $this->_redirectReferer();
    }

    /**
     * Remove item from compare list
     */
    public function removeAction()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        $product = Mage::getModel('catalog/product')
            ->load($productId);

        if($product->getId()) {
            $item = Mage::getModel('catalog/product_compare_item');
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
            } else {
                $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
            }

            $item->loadByProduct($product);

            if($item->getId()) {
                $item->delete();
            }
        }

        $this->_redirectReferer();
    }

    public function clearAction()
    {
        $items = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->setStoreId(Mage::app()->getStore()->getId());

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
        } else {
            $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
        }

        $items->load();

        $items->walk('delete');

        $this->_redirectReferer();
    }
 } // Class Mage_Catalog_CompareController end