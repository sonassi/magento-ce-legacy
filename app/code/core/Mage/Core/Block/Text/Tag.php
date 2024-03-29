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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */




/**
 * Base html block
 *
 * @version    1.0
 * @date       Thu Feb 08 05:56:43 EET 2007
 */

class Mage_Core_Block_Text_Tag extends Mage_Core_Block_Text
{
	protected function _construct()
	{
	    parent::_construct();
		$this->setTagParams(array());
	}

	function setTagParam($param, $value=null)
	{
	    if (is_array($param) && is_null($value)) {
	        foreach ($param as $k=>$v) {
	            $this->setTagParam($k, $v);
	        }
	    } else {
	        $params = $this->getTagParams();
	        $params[$param] = $value;
	        $this->setTagParams($params);
	    }
	    return $this;
	}

	function setContents($text)
	{
	    $this->setTagContents($text);
	    return $this;
	}

	function toHtml()
	{
	    $this->setText('<'.$this->getTagName().' ');
	    if ($this->getTagParams()) {
    	    foreach ($this->getTagParams() as $k=>$v) {
    	        $this->addText($k.'="'.$v.'" ');
    	    }
	    }

        $this->addText('>'.$this->getTagContents().'</'.$this->getTagName().'>'."\r\n");
	    return parent::toHtml();
	}
}// Class Mage_Core_Block_List END