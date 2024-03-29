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
 * Adminhtml entity sets controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Catalog_Product_SetController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_setTypeId();

        $this->loadLayout();
        $this->_setActiveMenu('catalog/sets');

        $this->_addBreadcrumb(Mage::helper('catalog')->__('Catalog'), Mage::helper('catalog')->__('Catalog'));
        $this->_addBreadcrumb(Mage::helper('catalog')->__('Manage Product Sets'), Mage::helper('catalog')->__('Manage Product Sets'));

        $this->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_attribute_set_toolbar_main'));
        $this->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_attribute_set_grid'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_setTypeId();
        $attributeSet = Mage::getModel('eav/entity_attribute_set')
            ->load($this->getRequest()->getParam('id'));

        if (!$attributeSet->getId()) {
            $this->_redirect('*/*/index');
            return;
        }

        Mage::register('current_attribute_set', $attributeSet);

        $this->loadLayout();
        $this->_setActiveMenu('catalog/sets');
        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);

        $this->_addBreadcrumb(Mage::helper('catalog')->__('Catalog'), Mage::helper('catalog')->__('Catalog'));
        $this->_addBreadcrumb(Mage::helper('catalog')->__('Manage Product Sets'), Mage::helper('catalog')->__('Manage Product Sets'));

        $this->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_attribute_set_main'));

        $this->renderLayout();
    }

    public function setGridAction()
    {
        $this->_setTypeId();
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/catalog_product_attribute_set_grid')->toHtml());
    }

    public function saveAction()
    {
        $this->_setTypeId();
        $response = new Varien_Object();
        $response->setError(0);

        $modelSet = Mage::getModel('eav/entity_attribute_set')
            ->setId($this->getRequest()->getParam('id'));

        if( $this->getRequest()->getParam('gotoEdit') ) {
            $modelSet = Mage::getModel('eav/entity_attribute_set');
            $modelSet->setAttributeSetName($this->getRequest()->getParam('attribute_set_name'))
                ->setEntityTypeId(Mage::registry('entityType'));
        } else {
            $data = Zend_Json_Decoder::decode($this->getRequest()->getPost('data'));
            $modelSet->organizeData($data);
        }

        try {
            $modelSet->save();
            if( $this->getRequest()->getParam('gotoEdit') == 1 ) {
                $modelSet->initFromSkeleton($this->getRequest()->getParam('skeleton_set'))
                    ->save();

                $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('id' => $modelSet->getId())));
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Attribute set successfully saved.'));
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Attribute set successfully saved.'));
                $response->setMessage(Mage::helper('catalog')->__('Attribute set successfully saved.'));
                $response->setUrl(Mage::getUrl('*/*/'));
            }
        } catch (Exception $e) {
            if( $this->getRequest()->getParam('gotoEdit') == 1 ) {
                #Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Error while saving this set. Set with the same name already exists.'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirectReferer();
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Attribute set with the same name already exists.'));
                $this->_initLayoutMessages('adminhtml/session');
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
                $response->setError(1);
            }
        }
        if( $this->getRequest()->getParam('gotoEdit') != 1 ) {
            $this->getResponse()->setBody($response->toJson());
        }
    }

    public function addAction()
    {
         $this->_setTypeId();

        $this->loadLayout();
        $this->_setActiveMenu('catalog/sets');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_attribute_set_toolbar_add'));

        $this->renderLayout();
    }

    public function deleteAction()
    {
        $setId = $this->getRequest()->getParam('id');
        try {
            Mage::getModel('eav/entity_attribute_set')
                ->setId($setId)
                ->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Attribute set was successfully removed.'));
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Error while deleting this set.'));
            $this->_redirectReferer();
        }
    }

    protected function _setTypeId()
    {

        Mage::register('entityType',
            Mage::getModel('catalog/product')->getResource()->getConfig()->getId());
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('catalog/attributes/sets');
    }

}
