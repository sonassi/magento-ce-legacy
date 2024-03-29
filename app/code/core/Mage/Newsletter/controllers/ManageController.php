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
 * @package    Mage_Newsletter
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customers newsletter subscription controller
 *
 * @category   Mage
 * @package    Mage_Newsletter
 */

class Mage_Newsletter_ManageController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();

       if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
       }
    }

	public function indexAction()
	{
        $this->loadLayout();
		$this->renderLayout();
	}

	public function saveAction()
	{
		try {
			Mage::getSingleton('customer/session')->getCustomer()
				->setIsSubscribed((boolean)$this->getRequest()->getParam('is_subscribed', false))
				->save();
			Mage::getSingleton('customer/session')->addSuccess(Mage::helper('newsletter')->__('The subscription was successfully saved'));
		}
		catch (Exception $e) {
			Mage::getSingleton('customer/session')->addError(Mage::helper('newsletter')->__('There was an error while saving your subscription'));
		}

		$this->_redirect('customer/account/');
	}
}// Class Mage_Customer_NewsletterController END