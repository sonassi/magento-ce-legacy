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
 * Adminhtml invoice create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Order_Invoice_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'invoice_id';
        $this->_controller  = 'sales_order_invoice';
        $this->_mode        = 'view';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        if ($this->getInvoice()->canCancel()) {
            $this->_addButton('cancel', array(
                'label'     => Mage::helper('sales')->__('Cancel'),
                'class'     => 'delete',
                'onclick'   => 'setLocation(\''.$this->getCancelUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->getOrder()->canCreditmemo()) {
            $this->_addButton('capture', array(
                'label'     => Mage::helper('sales')->__('Credit Memo'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getCreditMemoUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canCapture()) {
            $this->_addButton('capture', array(
                'label'     => Mage::helper('sales')->__('Capture'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getCaptureUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canVoid()) {
            $this->_addButton('void', array(
                'label'     => Mage::helper('sales')->__('Void'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getVoidUrl().'\')'
                )
            );
        }
    }

    /**
     * Retrieve invoice model instance
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function getInvoice()
    {
        return Mage::registry('current_invoice');
    }

    public function getHeaderText()
    {
        $header = Mage::helper('sales')->__('Invoice #%s',
            $this->getInvoice()->getIncrementId()
        );
        /*$header = Mage::helper('sales')->__('Invoice #%s | Order Date: %s | Customer Name: %s',
            $this->getInvoice()->getIncrementId(),
            $this->formatDate($this->getInvoice()->getOrder()->getCreatedAt(), 'medium', true),
            $this->getInvoice()->getOrder()->getCustomerName()
        );*/
        return $header;
    }

    public function getBackUrl()
    {
        return Mage::getUrl(
            '*/sales_order/view',
            array(
                'order_id'  => $this->getInvoice()->getOrderId(),
                'active_tab'=> 'order_invoices'
            ));
    }

    public function getCaptureUrl()
    {
        return $this->getUrl('*/*/capture', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getVoidUrl()
    {
        return $this->getUrl('*/*/void', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getCreditMemoUrl()
    {
        return $this->getUrl('*/sales_order_creditmemo/start', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
            'invoice_id'=> $this->getInvoice()->getId(),
        ));
    }
}