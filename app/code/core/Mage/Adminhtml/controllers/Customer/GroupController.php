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
 * Customer groups controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Customer_GroupController extends Mage_Adminhtml_Controller_Action
{
    protected function _initGroup()
    {
        Mage::register('current_group', Mage::getModel('customer/group'));
        if ($groupId = $this->getRequest()->getParam('id')) {
            Mage::registry('current_group')->load($groupId);
        }
        
    }
    /**
     * Customer groups list.
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('customer/group');
        $this->_addBreadcrumb(Mage::helper('customer')->__('Customers'), Mage::helper('customer')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('customer')->__('Customer Groups'), Mage::helper('customer')->__('Customer Groups'));

        $this->_addContent($this->getLayout()->createBlock('adminhtml/customer_group', 'group'));

        $this->renderLayout();
    }

    /**
     * Edit or create customer group.
     */
    public function newAction()
    {
        $this->_initGroup();
        $this->loadLayout();
        $this->_setActiveMenu('customer/group');
        $this->_addBreadcrumb(Mage::helper('customer')->__('Customers'), Mage::helper('customer')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('customer')->__('Customer Groups'), Mage::helper('customer')->__('Customer Groups'), Mage::getUrl('*/customer_group'));

        if (Mage::registry('current_group')->getId()) {
            $this->_addBreadcrumb(Mage::helper('customer')->__('Edit Group'), Mage::helper('customer')->__('Edit Customer Groups'));
        } else {
            $this->_addBreadcrumb(Mage::helper('customer')->__('New Group'), Mage::helper('customer')->__('New Customer Groups'));
        }

        $this->getLayout()->getBlock('content')
            ->append($this->getLayout()->createBlock('adminhtml/customer_group_edit', 'group')
                        ->setEditMode((bool)Mage::registry('current_group')->getId()));

        $this->renderLayout();
    }

    /**
     * Edit customer group action. Forward to new action.
     */
    public function editAction()
    {
        $this->_forward('new');
    }

    /**
     * Create or save customer group.
     */
    public function saveAction()
    {
        $customerGroup = Mage::getModel('customer/group');
        if ($id = (int)$this->getRequest()->getParam('id')) {
            $customerGroup->load($id);
        }
        
        if ($code = $this->getRequest()->getParam('code')) {
            try {
                $customerGroup->setCode($code)
                    ->setTaxClassId($this->getRequest()->getParam('tax_class'))
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('Customer Group was successfully saved'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerGroupData($customerGroup->getData());
                $this->getResponse()->setRedirect(Mage::getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }

    }

    /**
     * Delete customer group action
     */
    public function deleteAction()
    {
        $customerGroup = Mage::getModel('customer/group');
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $customerGroup->load($id);
                $customerGroup->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('Customer Group was successfully deleted'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect(Mage::getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        }

        $this->_redirect('*/customer_group');
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('customer/group');
    }


}
