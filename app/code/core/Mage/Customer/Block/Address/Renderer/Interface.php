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
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Address renderer interface
 *
 * @category   Mage
 * @package    Mage_Customer
 */
interface Mage_Customer_Block_Address_Renderer_Interface
{
    /**
     * Set format type object
     *
     * @param Varien_Object $type
     */
    function setType(Varien_Object $type);

    /**
     * Retrive format type object
     *
     * @return Varien_Object
     */
    function getType();

    /**
     * Render address
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     * @return mixed
     */
    function render(Mage_Customer_Model_Address_Abstract $address);
} // Class Mage_Customer_Block_Address_Renderer_Interface End