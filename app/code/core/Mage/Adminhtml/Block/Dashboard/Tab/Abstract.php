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
 * Adminhtml dashboard tab abstract
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

abstract class Mage_Adminhtml_Block_Dashboard_Tab_Abstract extends Mage_Adminhtml_Block_Widget
{

	protected $_tabBar = null;

	protected $_dataHelperName = null;

	public function __construct($attributes=array())
	{
		parent::__construct($attributes);
		$this->setTemplate($this->_getTabTemplate());
	}

	public function getCollection()
	{
	       return $this->getDataHelper()->getCollection();
	}

	public function getCount()
	{
	       return $this->getDataHelper()->getCount();
	}

	public function getDataHelper()
	{
	       return $this->helper($this->getDataHelperName());
	}

	public  function getDataHelperName()
	{
	       return $this->_dataHelperName;
	}

	public  function setDataHelperName($dataHelperName)
	{
	       $this->_dataHelperName = $dataHelperName;
	       return $this;
	}

	abstract protected function _getTabTemplate();
}// Class Mage_Adminhtml_Block_Dashboard_Abstract END