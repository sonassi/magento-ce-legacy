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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Order_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $totalTax = 0;

        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemTax = $orderItem->getTaxAmount();
            $orderItemQty = $orderItem->getQtyOrdered();

            if ($orderItemTax && $orderItemQty) {
                /**
                 * Resolve rounding problems
                 */
                if ($item->isLast()) {
                    $tax = $orderItemTax - $orderItem->getTaxInvoiced();
                }
                else {
                    $tax = $orderItemTax*$item->getQty()/$orderItemQty;
                    $tax = $invoice->getStore()->roundPrice($tax);
                }

                $item->setTaxAmount($tax);
                $totalTax += $tax;
            }
        }

        $invoice->setTaxAmount($totalTax);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax);
        return $this;
    }
}