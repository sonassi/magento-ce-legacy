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
 * @package    Mage_Paypal
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Paypal_Block_Express_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paypal/express/info.phtml');
    }

    public function getEmail()
    {
        $p = $this->getInfo();
        if ($p instanceof Mage_Sales_Model_Quote_Payment) {
            $email = $p->getQuote()->getBillingAddress()->getEmail();
        } elseif ($p instanceof Mage_Sales_Model_Order_Payment) {
            $email = $p->getOrder()->getBillingAddress()->getEmail();
        } else {
            $email = '';
        }
        return $email;
    }
}