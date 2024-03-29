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


class Mage_Sales_Model_Order_Creditmemo extends Mage_Core_Model_Abstract
{
    const STATE_OPEN        = 1;
    const STATE_REFUNDED    = 2;
    const STATE_CANCELED    = 3;

    const XML_PATH_UPDATE_EMAIL_TEMPLATE  = 'sales/email/creditmemo_comment_template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY  = 'sales/email/creditmemo_comment_identity';


    protected static $_states;

    protected $_items;
    protected $_order;

    /**
     * Initialize creditmemo resource model
     */
    protected function _construct()
    {
        $this->_init('sales/order_creditmemo');
    }

    /**
     * Retrieve Creditmemo configuration model
     *
     * @return Mage_Sales_Model_Order_Creditmemo_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('sales/order_creditmemo_config');
    }

    /**
     * Retrieve creditmemo store instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    /**
     * Declare order for creditmemo
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Mage_Sales_Model_Order_Creditmemo
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Retrieve the order the creditmemo for created for
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof Mage_Sales_Model_Order) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    /**
     * Retrieve billing address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * Retrieve shipping address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    public function getItemsCollection()
    {
        if (empty($this->_items)) {
            $this->_items = Mage::getResourceModel('sales/order_creditmemo_item_collection');

            if ($this->getId()) {
                $this->_items
                    ->addAttributeToSelect('*')
                    ->setCreditmemoFilter($this->getId())
                    ->load();
                foreach ($this->_items as $item) {
                    $item->setCreditmemo($this);
                }
            }
        }
        return $this->_items;
    }

    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] =  $item;
            }
        }
        return $items;
    }

    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId()==$itemId) {
                return $item;
            }
        }
        return false;
    }

    public function addItem(Mage_Sales_Model_Order_Creditmemo_Item $item)
    {
        $item->setCreditmemo($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Creditmemo totals collecting
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function collectTotals()
    {
        foreach ($this->getConfig()->getTotalModels() as $model) {
            $model->collect($this);
        }
        return $this;
    }

    public function canRefund()
    {
        if ($this->getState() != self::STATE_CANCELED &&
            $this->getState() != self::STATE_REFUNDED &&
            $this->getOrder()->getPayment()->canRefund()) {
            return true;
        }
        return false;
    }

    /**
     * Check creditmemo cancel action availability
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->getState() == self::STATE_OPEN;
    }

    /**
     * Check invice void action availability
     *
     * @return bool
     */
    public function canVoid()
    {
        $canVoid = false;
        return false;
        if ($this->getState() == self::STATE_REFUNDED) {
            $canVoid = $this->getCanVoidFlag();
            /**
             * If we not retrieve negative answer from payment yet
             */
            if (is_null($canVoid)) {
                $canVoid = $this->getOrder()->getPayment()->canVoid($this);
                if ($canVoid === false) {
                    $this->setCanVoidFlag(false);
                    $this->_saveBeforeDestruct = true;
                }
            }
            else {
                $canVoid = (bool) $canVoid;
            }
        }
        return $canVoid;
    }


    public function refund()
    {
        $this->setState(self::STATE_REFUNDED);
        $this->getOrder()->getPayment()->refound($this);

        $orderRefund = $this->getOrder()->getTotalRefunded()+$this->getGrandTotal();

        if ($orderRefund>$this->getOrder()->getTotalPaid()) {
            $availableRefund = $this->getOrder()->getTotalPaid()
                - $this->getOrder()->getTotalRefunded();
            Mage::throwException(
                Mage::helper('sales')->__('Maximum amount available to refund is %s',
                    $this->getOrder()->formatPrice($availableRefund))
            );
        }

        $this->getOrder()->setTotalRefunded($orderRefund);
        return $this;
    }

    /**
     * Cancel Creditmemo action
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function cancel()
    {
        $this->setState(self::STATE_CANCELED);
        foreach ($this->getAllItems() as $item) {
        	$item->cancel();
        }
        $this->getOrder()->getPayment()->cancelCreditmemo($this);
        return $this;
    }

    /**
     * Register creditmemo
     *
     * Apply to order, order items etc.
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function register()
    {
        if ($this->getId()) {
            Mage::throwException(
                Mage::helper('sales')->__('Can not register existing creditmemo')
            );
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->getQty()>0) {
                $item->register();
            }
            else {
                $item->isDeleted(true);
            }
        }

        if ($this->getRefundRequested()) {
            $creditmemo->getDoTransaction(true);
        }
        if ($this->getOfflineRequested()) {
            $creditmemo->getDoTransaction(false);
        }
        $this->refund();

        $state = $this->getState();
        if (is_null($state)) {
            $this->setState(self::STATE_OPEN);
        }
        return $this;
    }

    /**
     * Retrieve Creditmemo states array
     *
     * @return array
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = array(
                self::STATE_OPEN       => Mage::helper('sales')->__('Pending'),
                self::STATE_REFUNDED   => Mage::helper('sales')->__('Refunded'),
                self::STATE_CANCELED   => Mage::helper('sales')->__('Canceled'),
            );
        }
        return self::$_states;
    }

    /**
     * Retrieve Creditmemo state name by state identifier
     *
     * @param   int $stateId
     * @return  string
     */
    public function getStateName($stateId = null)
    {
        if (is_null($stateId)) {
            $stateId = $this->getState();
        }

        if (is_null(self::$_states)) {
            self::getStates();
        }
        if (isset(self::$_states[$stateId])) {
            return self::$_states[$stateId];
        }
        return Mage::helper('sales')->__('Unknown State');
    }

    public function setShippingAmount($amount)
    {
        $amount = $this->getStore()->roundPrice($amount);
        $this->setData('shipping_amount', $amount);
        return $this;
    }


    public function setAdjustmentPositive($amount)
    {
        $amount = $this->getStore()->roundPrice($amount);
        $this->setData('adjustment_positive', $amount);
        return $this;
    }

    public function setAdjustmentNegative($amount)
    {
        $amount = $this->getStore()->roundPrice($amount);
        $this->setData('adjustment_negative', $amount);
        return $this;
    }

    public function addComment($comment, $notify=false)
    {
        if (!($comment instanceof Mage_Sales_Model_Order_Creditmemo_Comment)) {
            $comment = Mage::getModel('sales/order_creditmemo_comment')
                ->setComment($comment)
                ->setIsCustomerNotified($notify);
        }
        $comment->setCreditmemo($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$comment->getId()) {
            $this->getCommentsCollection()->addItem($comment);
        }
        return $this;
    }

    public function getCommentsCollection()
    {
        if (is_null($this->_comments)) {
            $this->_comments = Mage::getResourceModel('sales/order_creditmemo_comment_collection');
            if ($this->getId()) {
                $this->_comments->addAttributeToSelect('*')
                    ->setCreditmemoFilter($this->getId())
                    ->load();
                foreach ($this->_comments as $comment) {
                    $comment->setCreditmemo($this);
                }
            }
        }
        return $this->_comments;
    }

    /**
     * Sending email with Credit Memo update information
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function sendUpdateEmail($comment='')
    {
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $this->getStoreId()),
                Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $this->getStoreId()),
                $this->getOrder()->getCustomerEmail(),
                $this->getOrder()->getBillingAddress()->getName(),
                array(
                    'order'  => $this->getOrder(),
                    'billing'=>$this->getOrder()->getBillingAddress(),
                    'creditmemo'=> $this,
                    'comment'=> $comment
                )
            );
        return $this;
    }
}