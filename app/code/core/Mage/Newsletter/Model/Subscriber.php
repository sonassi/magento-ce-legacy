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
 * Subscriber model
 *
 * @category   Mage
 * @package    Mage_Newsletter
 */
class Mage_Newsletter_Model_Subscriber extends Varien_Object
{
    const STATUS_SUBSCRIBED     = 1;
    const STATUS_NOT_ACTIVE     = 2;
    const STATUS_UNSUBSCRIBED   = 3;

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE       = 'newsletter/subscription/confirm_email_template';
    const XML_PATH_CONFIRM_EMAIL_IDENTITY       = 'newsletter/subscription/confirm_email_identity';
    const XML_PATH_SUCCESS_EMAIL_TEMPLATE       = 'newsletter/subscription/success_email_template';
    const XML_PATH_SUCCESS_EMAIL_IDENTITY       = 'newsletter/subscription/success_email_identity';
    const XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE   = 'newsletter/subscription/un_email_template';
    const XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY   = 'newsletter/subscription/un_email_identity';
    const XML_PATH_CONFIRMATION_FLAG            = 'newsletter/subscription/confirm';

    protected $_isStatusChanged = false;

    /**
     * Alias for getSubscriberId()
     *
     * @return int
     */
    public function getId()
    {
        return $this->getSubscriberId();
    }

    /**
     * Alias for setSubscriberId()
     *
     * @param int $value
     */
    public function setId($value)
    {
        return $this->setSubscriberId($value);
    }

    /**
     * Alias for getSubscriberConfirmCode()
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getSubscriberConfirmCode();
    }

    /**
     * Return link for confirmation of subscription
     *
     * @return string
     */
    public function getConfirmationLink() {
    	return Mage::helper('newsletter')->getConfirmationUrl($this);
    }

    public function getUnsubscriptionLink() {
    	return Mage::helper('newsletter')->getUnsubscribeUrl($this);
    }


    /**
     * Alias for setSubscriberConfirmCode()
     *
     * @param string $value
     */
    public function setCode($value)
    {
        return $this->setSubscriberConfirmCode($value);
    }

    /**
     * Alias for getSubscriberStatus()
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getSubscriberStatus();
    }

    /**
     * Alias for setSubscriberStatus()
     *
     * @param int
     */
    public function setStatus($value)
    {
        return $this->setSubscriberStatus($value);
    }

    /**
     * Set the error messages scope for subscription
     *
     * @param boolean $scope
     * @return unknown
     */

    public function setMessagesScope($scope)
    {
        $this->getResource()->setMessagesScope($scope);
        return $this;
    }

    /**
     * Alias for getSubscriberEmail()
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getSubscriberEmail();
    }

    /**
     * Alias for setSubscriberEmail()
     *
     * @param string $value
     */
    public function setEmail($value)
    {
        return $this->setSubscriberEmail($value);
    }

    /**
     * Set for status change flag
     *
     * @param boolean $value
     */
    public function setIsStatusChanged($value)
    {
    	$this->_isStatusChanged = (boolean) $value;
   		return $this;
    }

    /**
     * Return status change flag value
     *
     * @return boolean
     */
    public function getIsStatusChanged()
    {
    	return $this->_isStatusChanged;
    }

    public function isSubscribed()
    {
    	if($this->getId() && $this->getStatus()==self::STATUS_SUBSCRIBED) {
    		return true;
    	}

    	return false;
    }

    /**
     * Return resource model
     *
     * @return Mage_Subscriber_Model_Mysql4_Subscriber
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('newsletter/subscriber');
    }

    /**
     * Load subscriber data from resource model
     *
     * @param int $subscriberId
     */
    public function load($subscriberId)
    {
        $this->addData($this->getResource()->load($subscriberId));
        return $this;
    }

     /**
     * Load subscriber data from resource model by email
     *
     * @param int $subscriberId
     */
    public function loadByEmail($subscriberEmail)
    {
        $this->addData($this->getResource()->loadByEmail($subscriberEmail));
        return $this;
    }

    /**
     * Load subscriber info by customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function loadByCustomer(Mage_Customer_Model_Customer $customer)
    {
        $data = $this->getResource()->loadByCustomer($customer);
        $this->addData($data);
        if (!empty($data) && $customer->getId() && !$this->getCustomerId()) {
            $this->setCustomerId($customer->getId())->save();
        }
        return $this;
    }

    /**
     * Save subscriber data to resource model
     *
     */
    public function save()
    {
        return $this->getResource()->save($this);
    }

    /**
     * Deletes subscriber data
     */
    public function delete()
    {
        $this->getResource()->delete($this->getId());
        $this->setId(null);
    }

    public function randomSequence($length=32)
    {
        $id = '';
        $par = array();
        $char = array_merge(range('a','z'),range(0,9));
        $charLen = count($char)-1;
        for ($i=0;$i<$length;$i++){
            $disc = mt_rand(0, $charLen);
            $par[$i] = $char[$disc];
            $id = $id.$char[$disc];
        }
        return $id;
    }

    public function subscribe($email)
    {
    	$this->loadByEmail($email);
    	$customer = Mage::getModel('customer/customer')->loadByEmail($email);
    	$isNewSubscriber = false;

    	if (!$this->getSubscriberId() || $this->getSubscriberStatus()==self::STATUS_UNSUBSCRIBED) {
    		if (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG)) {
    			$this->setSubscriberStatus(self::STATUS_NOT_ACTIVE);
    		} else {
    			$this->setSubscriberStatus(self::STATUS_SUBSCRIBED);
    		}
			$this->setSubscriberConfirmCode($this->randomSequence());
    		$this->setSubscriberEmail($email);
    	}

    	$customerSession = Mage::getSingleton('customer/session');

        if ($customerSession->isLoggedIn()) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setSubscriberStatus(self::STATUS_SUBSCRIBED);
            $this->setCustomerId($customerSession->getCustomerId());
        } else if ($customer->getId()) {
            $this->setStoreId($customer->getStoreId());
            $this->setSubscriberStatus(self::STATUS_SUBSCRIBED);
            $this->setCustomerId($customer->getId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
            $isNewSubscriber = true;
        }

        $this->setIsStatusChanged(true);

        try {
        	$this->save();
	        if (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) && $isNewSubscriber) {
	       		$this->sendConfirmationRequestEmail();
	        } else {
	        	$this->sendConfirmationSuccessEmail();
	        }

        	return $this->getSubscriberStatus();
        } catch (Exception $e) {
        	throw new Exception($e->getMessage());
        }
    }

    public function unsubscribe($email)
    {
    	try {
    		$this->setSubscriberStatus(self::STATUS_UNSUBSCRIBED)->save();
    		$this->sendUnsubscriptionEmail();
    		return true;
    	} catch (Exception $e) {
    		return $e;
    	}
    }

    /**
     * Saving customer cubscription status
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Newsletter_Model_Subscriber
     */
    public function subscribeCustomer($customer)
    {
        $this->loadByCustomer($customer);

        if (!$customer->getIsSubscribed() && !$this->getId()) {
            // If subscription flag not seted or customer not subscriber
            // and no subscribe bellow
            return $this;
        }

        if($customer->hasIsSubscribed()) {
            $status = $customer->getIsSubscribed() ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED;
        } else {
            $status = $this->getStatus();
        }


        if($status != $this->getStatus()) {
            $this->setIsStatusChanged(true);
        }

        $this->setStatus($status);



        if(!$this->getId()) {
            $this->setStoreId($customer->getStoreId())
                ->setCustomerId($customer->getId())
                ->setEmail($customer->getEmail());
        } else {
            $this->setEmail($customer->getEmail());
        }

        $this->save();
        if ($this->getIsStatusChanged() && $status == self::STATUS_UNSUBSCRIBED) {
            $this->sendUnsubscriptionEmail();
        } elseif ($this->getIsStatusChanged() && $status == self::STATUS_SUBSCRIBED) {
            $this->sendConfirmationSuccessEmail();
        }
        return $this;
    }

    /**
     * Confirms subscriber newsletter
     *
     * @param string $code
     * @return boolean
     */
    public function confirm($code)
    {
    	if($this->getCode()==$code) {
    		$this->setStatus(self::STATUS_SUBSCRIBED)
    			->setIsStatusChanged(true)
    			->save();
    		return true;
    	}

    	return false;
    }

    /**
     * Mark receiving subscriber of queue newsletter
     *
     * @param  Mage_Newsletter_Model_Queue $queue
     * @return boolean
     */
    public function received(Mage_Newsletter_Model_Queue $queue)
    {
    	$this->getResource()->received($this,$queue);
    	return $this;
    }

    public function sendConfirmationRequestEmail()
    {
        if(!Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE) || !Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY))  {
            return $this;
        }

    	Mage::getModel('core/email_template')
    		->sendTransactional(
    		    Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE),
    		    Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY),
    		    $this->getEmail(),
    		    $this->getName(),
    		    array('subscriber'=>$this));

    	return $this;
    }

    public function sendConfirmationSuccessEmail()
    {
        if(!Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE) || !Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY))  {
            return $this;
        }

    	Mage::getModel('core/email_template')
    		->sendTransactional(
    		    Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
    		    Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
    		    $this->getEmail(),
    		    $this->getName(),
    		    array('subscriber'=>$this));
    	return $this;
    }

    public function sendUnsubscriptionEmail()
    {
        if(!Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE) || !Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY))  {
            return $this;
        }
        Mage::getModel('core/email_template')
    		->sendTransactional(
    		    Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE),
    		    Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY),
    		    $this->getEmail(),
    		    $this->getName(),
    		    array('subscriber'=>$this));
    	return $this;
    }
}