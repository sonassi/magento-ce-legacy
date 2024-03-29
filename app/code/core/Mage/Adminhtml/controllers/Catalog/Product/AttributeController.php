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
 * Catalog product attribute controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Catalog_Product_AttributeController extends Mage_Adminhtml_Controller_Action
{

    protected $_entityTypeId;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/attributes')
            ->_addBreadcrumb(Mage::helper('catalog')->__('Catalog'), Mage::helper('catalog')->__('Catalog'))
            ->_addBreadcrumb(Mage::helper('catalog')->__('Manage Product Attributes'), Mage::helper('catalog')->__('Manage Product Attributes'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_attribute'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = Mage::getModel('eav/entity_attribute');

        if ($id) {
            $model->load($id);

            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot edit this attribute'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getAttributeData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        Mage::register('entity_attribute', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('catalog')->__('Edit Product Attribute') : Mage::helper('catalog')->__('New Product Attribute'), $id ? Mage::helper('catalog')->__('Edit Product Attribute') : Mage::helper('catalog')->__('New Product Attribute'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit')->setData('action', Mage::getUrl('*/catalog_product_attribute/save')))
            ->_addLeft($this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit_tabs'))
            ->_addJs($this->getLayout()->createBlock('core/template')->setTemplate('catalog/product/attribute/js.phtml'))
            ->renderLayout();
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('attribute_id');
        $attribute = Mage::getModel('eav/entity_attribute')
            ->loadByCode($this->_entityTypeId, $attributeCode);

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Attribute with the same code already exists'));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('eav/entity_attribute');

            if ($id = $this->getRequest()->getParam('attribute_id')) {

                $model->load($id);

                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot update this attribute'));
                    Mage::getSingleton('adminhtml/session')->setAttributeData($data);
                    $this->_redirect('*/*/');
                    return;
                }
                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                //$data['is_global'] = $model->getIsGlobal();
            }

            /**
             * @todo need specify relations for properties
             */
            if (isset($data['frontend_input']) && $data['frontend_input'] == 'multiselect') {
                $data['backend_model'] = 'eav/entity_attribute_backend_array';
            }

            $model->addData($data);

            if (!$id) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Product attribute was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setAttributeData(false);
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAttributeData($data);
                $this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('attribute_id')) {
            $model = Mage::getModel('eav/entity_attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot delete this attribute'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Product attribute was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Unable to find an attribute to delete'));
        $this->_redirect('*/*/');
    }


    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('catalog/attributes/attributes');
    }

}
