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
 * @package    Mage_SalesRule
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_SalesRule_Model_Observer
{
    public function sales_order_afterPlace($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return $this;
        }

        $customerId = $order->getCustomerId();

        /**
         * If guest checkout for example
         */
        if (!$customerId) {
            return $this;
        }

        $ruleIds = explode(',', $order->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);

        $rule = Mage::getModel('salesrule/rule');
        $ruleCustomer = Mage::getModel('salesrule/rule_customer');

        foreach ($ruleIds as $ruleId) {
            if (!$ruleId) {
                continue;
            }

            $rule->load($ruleId);
            if ($rule->getId()) {
                $rule->setTimesUsed($rule->getTimesUsed() + 1);
                $rule->save();
            }

            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);

            if ($ruleCustomer->getId()) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed()+1);
            }
            else {
                $ruleCustomer
                ->setCustomerId($customerId)
                ->setRuleId($ruleId)
                ->setTimesUsed(1);
            }
            $ruleCustomer->save();
        }
    }
}