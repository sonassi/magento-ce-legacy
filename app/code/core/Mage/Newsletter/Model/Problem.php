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
 * Nesletter problem model
 *
 * @category   Mage
 * @package    Mage_Newsletter
 */

class Mage_Newsletter_Model_Problem extends Mage_Core_Model_Abstract
{
	protected  $_subscriber = null;
	
	protected function _construct() 
	{
		$this->_init('newsletter/problem');
	}
	
	public function addSubscriberData(Mage_Newsletter_Model_Subscriber $subscriber) 
	{
		$this->setSubscriberId($subscriber->getId());
	}
	
	public function addQueueData(Mage_Newsletter_Model_Queue $queue) 
	{
		$this->setQueueId($queue->getId());
	}
	
	public function addErrorData(Exception $e) 
	{
		$this->setProblemErrorCode($e->getCode());
		$this->setProblemErrorText($e->getMessage());
	}
	
	public function getSubscriber() 
	{
		if(!$this->getSubscriberId()) {
			return null;
		}
		
		
		if(is_null($this->_subscriber)) {
			$this->_subscriber = Mage::getModel('newsletter/subscriber')
				->load($this->getSubscriberId());
		}
		
		return $this->_subscriber;
	}
	
	public function unsubscribe() 
	{
		if($this->getSubscriber()) {
			$this->getSubscriber()->setSubscriberStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)
				->setIsStatusChanged(true)
				->save();
		}
	}
}// Class Mage_Newsletter_Model_Problem END