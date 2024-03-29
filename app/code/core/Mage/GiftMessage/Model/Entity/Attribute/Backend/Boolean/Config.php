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
 * @package    Mage_GiftMessage
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute for allowing of gift messages per item
 *
 * @category   Mage
 * @package    Mage_GiftMessage
 */
class Mage_GiftMessage_Model_Entity_Attribute_Backend_Boolean_Config extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Set attribute default value if value empty
     *
     * @param Varien_Object $object
     */
    public function afterLoad($object)
    {
    	if(!$object->hasData($this->getAttribute()->getAttributeCode())) {
    	    $object->setData($this->getAttribute()->getAttributeCode(), $this->getDefaultValue());
    	}
    }

    /**
     * Set attribute default value if value empty
     *
     * @param Varien_Object $object
     */
    public function beforeSave($object)
    {
        if($object->hasData($this->getAttribute()->getAttributeCode())
            && $object->getData($this->getAttribute()->getAttributeCode()) == $this->getDefaultValue()) {
            $object->unsData($this->getAttribute()->getAttributeCode());
        }
    }
} // Class Mage_GiftMessage_Model_Entity_Attribute_Backend_Boolean_Config End