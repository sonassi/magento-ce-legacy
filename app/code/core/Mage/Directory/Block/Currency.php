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
 * @package    Mage_Directory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Currency dropdown block
 *
 * @category   Mage
 * @package    Mage_Directory
 */
class Mage_Directory_Block_Currency extends Mage_Core_Block_Template
{
    /**
     * Retrieve count of currencies
     *
     * @return int
     */
    public function getCurrencyCount()
    {
        return count($this->getCurrencies());
    }

    public function getCurrencies()
    {
        $currencies = $this->getData('currencies');
        if (is_null($currencies)) {
            $currencies = array();
            $codes = Mage::app()->getStore()->getAvailableCurrencyCodes();
            if (is_array($codes) && count($codes)) {
                $rates = Mage::getModel('directory/currency')->getCurrencyRates(
                    Mage::app()->getStore()->getBaseCurrency(),
                    $codes
                );
                foreach ($codes as $code) {
                    if (isset($rates[$code])) {
                        $currencies[$code] = Mage::app()->getLocale()->getLocale()
                            ->getTranslation($code, 'nametocurrency');
                    }
                }
            }

            $this->setData('currencies', $currencies);
        }
        return $currencies;
    }

    public function getSwitchUrl()
    {
        return $this->getUrl('directory/currency/switch');
    }

    public function getCurrentCurrencyCode()
    {
        $code = $this->getData('current_currency_code');
        if (is_null($code)) {
            $code = Mage::app()->getStore()->getCurrentCurrencyCode();
            $this->setData('current_currency_code', $code);
        }
        return $code;
    }
}
