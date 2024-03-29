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
 * Store model
 *
 */
class Mage_Core_Model_Store extends Mage_Core_Model_Abstract
{
    const XML_PATH_USE_REWRITES        = 'web/seo/use_rewrites';
    const XML_PATH_UNSECURE_BASE_URL   = 'web/unsecure/base_url';
    const XML_PATH_SECURE_BASE_URL     = 'web/secure/base_url';
    const XML_PATH_SECURE_IN_FRONTEND  = 'web/secure/use_in_frontend';
    const XML_PATH_SECURE_IN_ADMINHTML = 'web/secure/use_in_adminhtml';

    const URL_TYPE_LINK  = 'link';
    const URL_TYPE_WEB   = 'web';
    const URL_TYPE_SKIN  = 'skin';
    const URL_TYPE_JS    = 'js';
    const URL_TYPE_MEDIA = 'media';

    const DEFAULT_CODE = 'default';

    protected $_priceFilter;

    protected $_website;

    protected $_configCache = array();

    protected $_dirCache = array();

    protected $_urlCache = array();

    protected $_baseUrlCache = array();

    protected $_session;

    public function __construct()
    {
        parent::__construct();
    }

    protected function _construct()
    {
        $this->_init('core/store');
    }

    /**
     * Retrieve store session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        if (!$this->_session) {
            $this->_session = Mage::getModel('core/session')
                ->init('store_'.$this->getCode());
        }
        return $this->_session;
    }

    /**
     * Loading store data
     *
     * @param   mixed $id
     * @param   string $field
     * @return  Mage_Core_Model_Store
     */
    public function load($id, $field=null)
    {
        if (!is_numeric($id) && is_null($field)) {
            $this->_getResource()->load($this, $id, 'code');
            return $this;
        }
        return parent::load($id, $field);
    }

    /**
     * Loading store configuration data
     *
     * @param   string $code
     * @return  Mage_Core_Model_Store
     */
    public function loadConfig($code)
    {
        if (is_numeric($code)) {
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeCode=>$store) {
                if ((int)$store->system->store->id==$code) {
                    $code = $storeCode;
                    break;
                }
            }
        } else {
            $store = Mage::getConfig()->getNode('stores/'.$code);
        }
        if (!empty($store)) {
            $this->setCode($code);
            $id = (int)$store->system->store->id;
            $this->setId($id)->setStoreId($id);
            $this->setWebsiteId((int)$store->system->website->id);
        }
        return $this;
    }

    /**
     * Retrieve store identifier
     *
     * @return int
     */
    public function getId()
    {
        if (is_null(parent::getId())) {
            $this->setId($this->getConfig('system/store/id'));
        }
        return parent::getId();
    }

    /**
     * Retrieve store code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Retrieve store configuration data
     *
     * @param   string $path
     * @param   string $scope
     * @return  string|null
     */
    public function getConfig($path) {
        if (isset($this->_configCache[$path])) {
            return $this->_configCache[$path];
        }

        $config = Mage::getConfig();

        $fullPath = 'stores/'.$this->getCode().'/'.$path;
        $data = $config->getNode($fullPath);
        if (!$data) {
            Mage::log('Invalid store configuration path: '.$path);
            return null;
        }
        if (!$data->children()) {
            $value = $this->processSubst((string)$data);
        } else {
            $value = array();

            foreach ($data->children() as $k=>$v) {
                if ($v->children()) {
                    $value[$k] = $v;
                } else {
                    $value[$k] = $this->processSubst((string)$v);
                }
            }
        }

        $this->_configCache[$path] = $value;
        return $value;
    }

    /**
     * Retrieve store website
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return Mage::app()->getWebsite($this->getConfig('system/website/id'));
    }

    public function processSubst($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        if (strpos($value, '{{unsecure_base_url}}')!==false) {
            $unsecureBaseUrl = $this->getConfig('web/unsecure/base_url');
            $value = str_replace('{{unsecure_base_url}}', $unsecureBaseUrl, $value);

        } elseif (strpos($value, '{{secure_base_url}}')!==false) {
            $secureBaseUrl = $this->getConfig('web/secure/base_url');
            $value = str_replace('{{secure_base_url}}', $secureBaseUrl, $value);
        } elseif (strpos($value, '{{')!==false) {
            $value = Mage::getConfig()->substDistroServerVars($value);
        }
        return $value;
    }

    public function getDefaultBasePath()
    {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return '/';
        }
        return rtrim(Mage::app()->getRequest()->getBasePath().'/').'/';
    }

    public function getDatashareStores($key)
    {
        // TODO store level data sharing configuration in next version
        // if ($stores = $this->getConfig('advanced/datashare/'.$key)) {
        if ($stores = $this->getConfig('advanced/datashare/'.$key)) {
            return explode(',', $stores);
        } else {
            $this->updateDatasharing($key);
            if ($stores = $this->getConfig('advanced/datashare/'.$key)) {
                return explode(',', $stores);
            }
        }
        return $this->getWebsite()->getStoresIds();
    }

    public function updateDatasharing()
    {
        $this->_getResource()->updateDatasharing();
    }

    /**
     * Retrieve url using store configuration specific
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route='', $params=array())
    {
        $url = Mage::getModel('core/url')
            ->setStore($this);
        return $url->getUrl($route, $params);
    }


    public function getBaseUrl($type=self::URL_TYPE_LINK, $secure=null)
    {
        $cacheKey = $type.'/'.(is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
            switch ($type) {
                case self::URL_TYPE_LINK:
                    $secure = (bool)$secure;
                    $url = $this->getConfig('web/'.($secure ? 'secure' : 'unsecure').'/base_link_url');
                    if (!$this->getId()
                        || !$this->getConfig(self::XML_PATH_USE_REWRITES)
                        || !Mage::app()->isInstalled()) {
                        $url .= basename($_SERVER['SCRIPT_FILENAME']).'/';
                        #$url .= 'index.php/';
                    }
                    break;

                case self::URL_TYPE_WEB:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
                    $url = $this->getConfig('web/'.($secure ? 'secure' : 'unsecure').'/base_url');
                    break;

                case self::URL_TYPE_SKIN:
                case self::URL_TYPE_MEDIA:
                case self::URL_TYPE_JS:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
                    $url = $this->getConfig('web/'.($secure ? 'secure' : 'unsecure').'/base_'.$type.'_url');
                    break;

                default:
                    throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid base url type'));
            }
            $this->_baseUrlCache[$cacheKey] = rtrim($url, '/').'/';
        }

        return $this->_baseUrlCache[$cacheKey];
    }

    public function isCurrentlySecure()
    {
        if (!empty($_SERVER['HTTPS'])) {
            return true;
        }

        if (Mage::app()->isInstalled()) {
            $secureBaseUrl = Mage::getStoreConfig('web/secure/base_route_url');
            if (!$secureBaseUrl) {
                return false;
            }
            $uri = Zend_Uri::factory($secureBaseUrl);
            list($secureScheme) = explode(':', $secureBaseUrl);
            return $uri->getScheme() == 'https'
                && $uri->getPort() == $_SERVER['SERVER_PORT'];
        } else {
            return 443 == $_SERVER['SERVER_PORT'];
        }
    }

    /*************************************************************************************
     * Store currency interface
     */

    /**
     * Retrieve store base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }

    /**
     * Retrieve store base currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency()
    {
        $currency = $this->getData('base_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('directory/currency')->load($this->getBaseCurrencyCode());
            $this->setData('base_currency', $currency);
        }
        return $currency;
    }

    /**
     * Get default store currency code
     *
     * @return string
     */
    public function getDefaultCurrencyCode()
    {
        $result = $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT);
        return $result;
    }

    /**
     * Retrieve store default currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getDefaultCurrency()
    {
        $currency = $this->getData('default_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('directory/currency')->load($this->getDefaultCurrencyCode());
            $this->setData('default_currency', $currency);
        }
        return $currency;
    }

    /**
     * Set current store currency code
     *
     * @param   string $code
     * @return  string
     */
    public function setCurrentCurrencyCode($code)
    {
        $code = strtoupper($code);
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            $this->_getSession()->setCurrencyCode($code);
        }
        return $this;
    }

    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        $code = $this->_getSession()->getCurrencyCode();
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            return $code;
        }
        return $this->getDefaultCurrencyCode();
    }

    /**
     * Get allowed store currency codes
     *
     * @return array
     */
    public function getAvailableCurrencyCodes()
    {
        $codes = $this->getData('available_currency_codes');
        if (is_null($codes)) {
            $codes = explode(',', $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_ALLOW));
            $this->setData('available_currency_codes', $codes);
        }
        return $codes;
    }

    /**
     * Retrieve store current currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrentCurrency()
    {
        $currency = $this->getData('current_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('directory/currency')->load($this->getCurrentCurrencyCode());
            $this->setData('current_currency', $currency);
        }
        return $currency;
    }

    public function getCurrentCurrencyRate()
    {
        return $this->getBaseCurrency()->getRate($this->getCurrentCurrency());
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param   double $price
     * @return  double
     */
    public function convertPrice($price, $format=false)
    {
        if ($this->getCurrentCurrency() && $this->getBaseCurrency()) {
            $value = $this->getBaseCurrency()->convert($price, $this->getCurrentCurrency());
        } else {
            $value = $price;
        }
        $value = $this->roundPrice($value);

        if ($this->getCurrentCurrency() && $format) {
            $value = $this->formatPrice($value);
        }
        return $value;
    }

    public function roundPrice($price)
    {
        return round($price, 2);
    }

    /**
     * Format price with currency filter (taking rate into consideration)
     *
     * @param   double $price
     * @return  string
     */
    public function formatPrice($price)
    {
        if ($this->getCurrentCurrency()) {
            return $this->getCurrentCurrency()->format($price);
        }
        return $price;
    }

    /**
     * Get store price filter
     *
     * @return unknown
     */
    public function getPriceFilter()
    {
        if (!$this->_priceFilter) {
            if ($this->getBaseCurrency() && $this->getCurrentCurrency()) {
                $this->_priceFilter = $this->getCurrentCurrency()->getFilter();
                $this->_priceFilter->setRate($this->getBaseCurrency()->getRate($this->getCurrentCurrency()));
            }
            elseif($this->getDefaultCurrency()) {
                $this->_priceFilter = $this->getDefaultCurrency()->getFilter();
            }
            else {
                $this->_priceFilter = new Varien_Filter_Sprintf('%s', 2);
            }
        }
        return $this->_priceFilter;
    }
}
