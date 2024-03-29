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
 * Customer address config
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Mage_Customer_Model_Address_Config extends Mage_Core_Model_Config_Base
{
    const DEFAULT_ADDRESS_RENDERER = 'customer/address_renderer_default';
    const DEFAULT_ADDRESS_FORMAT = 'oneline';

    protected $_types;
    protected $_defaultType;

    public function __construct()
    {
        parent::__construct(Mage::getConfig()->getNode('global/customer/address'));
    }

    public function getFormats()
    {
        if(is_null($this->_types)) {
            $this->_types = array();
            foreach($this->getNode('formats')->children() as $typeCode=>$typeConfig) {
                $type = new Varien_Object();
                $type->setCode($typeCode)
                    ->setTitle($typeConfig->title)
                    ->setDefaultFormat($typeConfig->defaultFormat);

                $renderer = $typeConfig->renderer;
                if (!$renderer) {
                    $renderer = self::DEFAULT_ADDRESS_RENDERER;
                }

                $type->setRenderer(
                    Mage::helper('customer/address')
                        ->getRenderer($renderer)->setType($type)
                );

                $this->_types[] = $type;
            }
        }

        return $this->_types;
    }

    protected function _getDefaultFormat()
    {
        if(is_null($this->_defaultType)) {
            $this->_defaultType = new Varien_Object();
            $this->_defaultType->setCode('default')
                ->setDefaultFormat('{{var firstname}} {{var lastname}}, {{var street}}, {{var city}}, {{var regionName}} {{var postcode}}, {{var country}}');

            $this->_defaultType->setRenderer(
                Mage::helper('customer/address')
                    ->getRenderer(self::DEFAULT_ADDRESS_RENDERER)->setType($this->_defaultType)
            );
        }
        return $this->_defaultType;
    }

    public function getFormatByCode($typeCode)
    {
        foreach($this->getFormats() as $type) {
            if($type->getCode()==$typeCode) {
                return $type;
            }
        }
        return $this->_getDefaultFormat();
    }
} // Class Mage_Customer_Model_Address_Config End