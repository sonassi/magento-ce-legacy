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
 * Customer admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCustomer($idFieldName = 'id')
    {
        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     * Customers list action
     */
    public function indexAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('customer/manage');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/customer', 'customer')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Customers'), Mage::helper('adminhtml')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Customers'), Mage::helper('adminhtml')->__('Manage Customers'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_grid')->toHtml());
    }

    /**
     * Customer edit action
     */
    public function editAction()
    {
        $this->_initCustomer();
        $this->loadLayout();

        $customer = Mage::registry('current_customer');

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getCustomerData(true);

        if (isset($data['account'])) {
            $customer->addData($data['account']);
        }
        if (isset($data['address']) && is_array($data['address'])) {
            $collection = $customer->getAddressCollection();
            foreach ($data['address'] as $addressId => $address) {
                $addressModel = Mage::getModel('customer/address')->setData($address)
                    ->setId($addressId);
            	$collection->addItem($addressModel);
            }

            $customer->setLoadedAddressCollection($collection);
        }

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('customer/new');

        /**
         * Append customer edit block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/customer_edit')
        );

        /**
         * Append customer edit tabs to left block
         */
        $this->_addLeft($this->getLayout()->createBlock('adminhtml/customer_edit_tabs'));

        $this->renderLayout();
    }

    /**
     * Create new customer action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Delete customer action
     */
    public function deleteAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            try {
                $customer->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Customer was deleted'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/customer');
    }

    /**
     * Save customer action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $this->_initCustomer('customer_id');
            $customer = Mage::registry('current_customer');

            // Prepare customer saving data
            if (isset($data['account'])) {
                $customer->addData($data['account']);
            }

            if (!$customer->getId()) {
                $customer->setCreatedIn(0); // Created from admin
            }

            if (isset($data['address'])) {
                // unset template data
                if (isset($data['address']['_template_'])) {
                    unset($data['address']['_template_']);
                }

                foreach ($data['address'] as $index => $addressData) {
                    $address = Mage::getModel('customer/address');
                    $address->setData($addressData);

                    if ($addressId = (int) $index) {
                        $address->setId($addressId);
                    }
                    /**
                     * We need set post_index for detect default addresses
                     */
                    $address->setPostIndex($index);
                    $customer->addAddress($address);
                }
            }

            if(isset($data['subscription'])) {
                $customer->setIsSubscribed(true);
            } else {
                $customer->setIsSubscribed(false);
            }

            $isNewCustomer = !$customer->getId();
            try {
                if ($customer->getPassword() == 'auto') {
                    $customer->setPassword($customer->generatePassword());
                }

                $customer->save();
                if ($isNewCustomer && $customer->hasData('sendemail')) {
                    $customer->sendNewAccountEmail();
                }

                if ($newPassword = $customer->getNewPassword()) {
                    if ($newPassword == 'auto') {
                        $newPassword = $customer->generatePassword();
                    }
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Customer was successfully saved'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerData($data);
                $this->getResponse()->setRedirect(Mage::getUrl('*/customer/edit', array('id'=>$customer->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/customer'));
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'customers.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/customer_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'customers.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/customer_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content)
    {
        header('HTTP/1.1 200 OK');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Last-Modified: '.date('r'));
        header("Accept-Ranges: bytes");
        header("Content-Length: ".sizeof($content));
        header("Content-type: application/octet-stream");
        echo $content;
        exit;
    }

    /**
     * Customer orders grid
     *
     */
    public function ordersAction() {
        $this->_initCustomer();
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_orders')->toHtml());
    }

    /**
     * Customer newsletter grid
     *
     */
    public function newsletterAction()
    {
        $this->_initCustomer();
        $subscriber = Mage::getModel('newsletter/subscriber')
            ->loadByCustomer(Mage::registry('current_customer'));

        Mage::register('subscriber', $subscriber);
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_newsletter_grid')->toHtml());
    }

    public function wishlistAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            if($itemId = (int) $this->getRequest()->getParam('delete')) {
            	try {
	            	Mage::getModel('wishlist/item')->load($itemId)
	            		->delete();
            	}
            	catch (Exception $e) {
            		//
            	}
            }
        }
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_wishlist')->toHtml());
    }

    public function cartAction()
    {
        $this->_initCustomer();
        if ($deleteItemId = $this->getRequest()->getPost('delete')) {
            $quote = Mage::getResourceModel('sales/quote_collection')
                ->loadByCustomerId(Mage::registry('current_customer')->getId());
            $quote->removeItem($deleteItemId);
            $quote->save();
        }
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/customer_edit_tab_cart')->toHtml());
    }

    public function tagGridAction()
    {
        $this->_initCustomer();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/customer_edit_tab_tag', 'admin.customer.tags')
                ->setCustomerId(Mage::registry('current_customer'))
                ->toHtml()
        );
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(0);

        $accountData = $this->getRequest()->getPost('account');

        $customer = Mage::getModel('customer/customer');
        if ($id = $this->getRequest()->getParam('id')) {
            $customer->load($id);
        }

        # Checking if we received email. If not - ERROR
        if( !($accountData['email']) ) {
            $response->setError(1);
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("Please fill in 'email' field."));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        } else {
            # Trying to load customer with the same email and return error message
            # if customer with the same email address exisits
            $checkCustomer = Mage::getModel('customer/customer');
            $checkCustomer->loadByEmail($accountData['email']);
            if( $checkCustomer->getId() && ($checkCustomer->getId() != $customer->getId()) ) {
                $response->setError(1);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Customer with the same email already exists.'));
                $this->_initLayoutMessages('adminhtml/session');
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->setBody($response->toJson());
    }

    public function massSubscribeAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s)'));

        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setIsSubscribed(true);
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($customersIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massUnsubscribeAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s)'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setIsSubscribed(false);
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($customersIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s)'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($customersIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    public function massAssignGroupAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s)'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setGroupId($this->getRequest()->getParam('group'));
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($customersIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
    	//print $this->getRequest()->getActionName();
	    return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
