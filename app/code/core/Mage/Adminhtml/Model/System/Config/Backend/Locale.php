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
 * Config locale allowed currencies backend
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Model_System_Config_Backend_Locale
{
    public function afterSave(Varien_Object $configData)
    {
        $collection = Mage::getModel('core/config_data')
            ->getCollection()
            ->addPathFilter('currency/options');

        $values     = split(',', $configData->getValue());
        $exceptions = array();
        foreach ($collection as $data) {
            if (preg_match('/(base|default)$/', $data->getPath(), $match)) {
                if (!in_array($data->getValue(), $values)) {
                    $currencyName = Mage::app()->getLocale()->currency($data->getValue())->getName();
                    if ($match[1] == 'base') {
                        $fieldName = Mage::helper('adminhtml')->__('Base currency');
                    }
                    else {
                        $fieldName = Mage::helper('adminhtml')->__('Display default currency');
                    }

                    switch ($data->getScope()) {
                        case 'default':
                            $scopeName = Mage::helper('adminhtml')->__('Default scope');
                            break;

                        case 'website':
                            $websiteName = Mage::getModel('core/website')->load($data->getScopeId())->getName();
                            $scopeName = Mage::helper('adminhtml')->__('website(%s) scope', $websiteName);
                            break;

                        case 'store':
                            $storeName = Mage::getModel('core/store')->load($data->getScopeId())->getName();
                            $scopeName = Mage::helper('adminhtml')->__('store(%s) scope', $storeName);
                            break;
                    }

                    $exceptions[] = Mage::helper('adminhtml')->__('Currency "%s" is used as %s in %s',
                        $currencyName,
                        $fieldName,
                        $scopeName
                    );
                }
            }
        }
        if ($exceptions) {
            Mage::throwException(join("\n", $exceptions));
        }

        return $configData;
    }
}