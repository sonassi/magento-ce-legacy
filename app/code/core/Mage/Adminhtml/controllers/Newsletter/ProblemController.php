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
 * Adminhtml newsletter subscribers controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Newsletter_ProblemController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
	    if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->getLayout()->getMessagesBlock()->setMessages(
            Mage::getSingleton('adminhtml/session')->getMessages(true)
        );
        $this->loadLayout();

		$this->_setActiveMenu('newsletter/problem');


		$this->_addBreadcrumb(Mage::helper('newsletter')->__('Newsletter Problem Reports'), Mage::helper('newsletter')->__('Newsletter Problem Reports'));

		$this->_addContent(
			$this->getLayout()->createBlock('adminhtml/newsletter_problem', 'problem')
		);

		$this->renderLayout();
	}

	public function gridAction()
    {
    	if($this->getRequest()->getParam('_unsubscribe')) {
    		$problems = (array) $this->getRequest()->getParam('problem', array());
    		if (count($problems)>0) {
    			$collection = Mage::getResourceModel('newsletter/problem_collection');
    			$collection
    				->addSubscriberInfo()
    				->addFieldToFilter($collection->getResource()->getIdFieldName(),
    								   array('in'=>$problems))
    				->load();

    			$collection->walk('unsubscribe');
    		}

    		Mage::getSingleton('adminhtml/session')
    			->addSuccess(Mage::helper('newsletter')->__('Selected problem subscribers successfully unsubscribed'));
    	}

    	if($this->getRequest()->getParam('_delete')) {
    		$problems = (array) $this->getRequest()->getParam('problem', array());
    		if (count($problems)>0) {
    			$collection = Mage::getResourceModel('newsletter/problem_collection');
    			$collection
    				->addFieldToFilter($collection->getResource()->getIdFieldName(),
    								   array('in'=>$problems))
    				->load();
    			$collection->walk('delete');
    		}

    		Mage::getSingleton('adminhtml/session')
    			->addSuccess(Mage::helper('newsletter')->__('Selected problems successfully deleted'));
    	}
    	    	$this->getLayout()->getMessagesBlock()->setMessages(Mage::getSingleton('adminhtml/session')->getMessages(true));

    	$grid = $this->getLayout()->createBlock('adminhtml/newsletter_problem_grid');
    	$this->getResponse()->setBody($grid->toHtml());
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('newsletter/problem');
    }

}// Class Mage_Adminhtml_Newsletter_ProblemController END
