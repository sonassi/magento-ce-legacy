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
 * Core URL helper
 *
 */
class Mage_Core_Helper_Url extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve current url
     * 
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_getUrl('*/*/*', array('_current'=>true));
    }
    
    /**
     * Retrieve current url in base64 encoding
     *
     * @return string
     */
    public function getCurrentBase64Url()
    {
        return base64_encode($this->getCurrentUrl());
    }
    
    /**
     * Retrieve homepage url
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return Mage::getBaseUrl();
    }
    
    protected function _prepareString($string)
    {
        $string = preg_replace('#[^0-9a-z]+#i', '-', $string);
        $string = strtolower($string);
        $string = trim($string, '-');
    	
        return $string;
    }
}
