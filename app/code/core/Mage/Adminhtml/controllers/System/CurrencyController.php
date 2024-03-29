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
 * Currency controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_System_CurrencyController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init currency by currency code from request
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initCurrency()
    {
        $code = $this->getRequest()->getParam('currency');
        $currency = Mage::getModel('directory/currency')
            ->load($code);

        Mage::register('currency', $currency);
        return $this;
    }

    /**
     * Currency management main page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/currency');
        $this->_addContent($this->getLayout()->createBlock('adminhtml/system_currency'));
        $this->renderLayout();
    }

    public function fetchRatesAction()
    {
        try {
            $service = $this->getRequest()->getParam('rate_services');
            if( !$service ) {
                throw new Exception(Mage::helper('adminhtml')->__('Invalid Import Service Specified'));
            }
            try {
                $importModel = Mage::getModel(Mage::getConfig()->getNode('global/currency/import/services/' . $service . '/model')->asArray());
            } catch (Exception $e) {
                Mage::throwException(Mage::helper('adminhtml')->__('Unable to initialize import model'));
            }
            $rates = $importModel->fetchRates();
            $errors = $importModel->getMessages();
            if( sizeof($errors) > 0 ) {
                foreach ($errors as $error) {
                	Mage::getSingleton('adminhtml/session')->addWarning($error);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('All possible rates were fetched, click on "Save" to apply'));
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('All rates were fetched, click on "Save" to apply'));
            }

            Mage::getSingleton('adminhtml/session')->setRates($rates);
        }
        catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function saveRatesAction()
    {
        $data = $this->getRequest()->getParam('rate');
        if( is_array($data) ) {
            try {
                foreach ($data as $currencyCode => $rate) {
                    foreach( $rate as $currencyTo => $value ) {
                        $value = abs($value);
                        if( $value == 0 ) {
                            Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('adminhtml')->__('Invalid input data for %s => %s rate', $currencyCode, $currencyTo));
                        }
                    }
                }

                Mage::getModel('directory/currency')->saveRates($data);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('All valid rates successfully saved'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('system/currency');
    }
}
