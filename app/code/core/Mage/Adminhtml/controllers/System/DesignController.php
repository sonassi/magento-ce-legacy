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


class Mage_Adminhtml_System_DesignController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->_addContent($this->getLayout()->createBlock('adminhtml/system_design'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/system_design_grid')->toHtml());
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);

        $id  = (int) $this->getRequest()->getParam('id');
        $design    = Mage::getModel('core/design');

        if ($id) {
            $design->load($id);
        }

        Mage::register('design', $design);

        $this->_addContent($this->getLayout()->createBlock('adminhtml/system_design_edit'));
        $this->_addLeft($this->getLayout()->createBlock('adminhtml/system_design_edit_tabs', 'design_tabs'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
        	$id = (int) $this->getRequest()->getParam('id');

        	$design = Mage::getModel('core/design');
        	if ($id) {
        	    $design->load($id);
        	}

            $design->addData($data['design']);
            try {
                $design->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Design change saved'));
            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage())
                    ->setDesignData($data);
                $this->_redirect('*/*/edit', array('id'=>$design->getId()));
                return;
            }
        }

        $this->_redirect('*/*/');
    }


    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $design = Mage::getModel('core/design')
                ->setId($id);

            try {
                $design->delete();

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($this->__('Design change deleted'));
            } catch (Mage_Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addException($e, $this->__("Can't delete design change"));
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
    }
}