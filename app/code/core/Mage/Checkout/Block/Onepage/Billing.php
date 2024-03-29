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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 */
class Mage_Checkout_Block_Onepage_Billing extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {
        $this->getCheckout()->setStepData('billing', array('label'=>Mage::helper('checkout')->__('Billing Information')));
        if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('billing', 'allow', true);
        }
        parent::_construct();
    }

    // CHECK with Moshe / Mischa
    public function getPickupOrUseForShipping()
    {
        if ($this->getQuote()->getIsVirtual()) {
            return false;
        }

        if ($this->getQuote()->getShippingAddress()) {
        	if ($this->getQuote()->getShippingAddress()->getSameAsBilling()) {
        		return 1;
        	} else {
        		return 0;
        	}
        } else {
        	return 2;
        }
    }

    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    function getAddress() {
        if (!$this->isCustomerLoggedIn()) {
            return $this->getQuote()->getBillingAddress();
        } else {
            return Mage::getModel('sales/quote_address');
        }
    }
}