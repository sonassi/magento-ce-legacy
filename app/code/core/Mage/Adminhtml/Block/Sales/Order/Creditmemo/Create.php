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
 * Adminhtml creditmemo create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'sales_order_creditmemo';
        $this->_mode = 'create';

        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('save');

        /*$this->_addButton('submit_creditmemo', array(
            'label'     => Mage::helper('sales')->__('Submit Credit Memo'),
            'class'     => 'save submit-button',
            'onclick'   => '$(\'edit_form\').submit()',
            )
        );*/

    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }

    public function getHeaderText()
    {
        $header = Mage::helper('sales')->__('New Credit Memo for Order #%s',
            $this->getCreditmemo()->getOrder()->getRealOrderId()
        );
        /*$header = Mage::helper('sales')->__('New Credit Memo for Order #%s | Order Date: %s | Customer Name: %s',
            $this->getCreditmemo()->getOrder()->getRealOrderId(),
            $this->formatDate($this->getCreditmemo()->getOrder()->getCreatedAt(), 'medium', true),
            $this->getCreditmemo()->getOrder()->getCustomerName()
        );*/
        return $header;
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/sales_order/view', array('order_id'=>$this->getCreditmemo()->getOrderId()));
    }
}