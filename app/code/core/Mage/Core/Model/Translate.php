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
 * Translate model
 *
 */
class Mage_Core_Model_Translate
{
    const CSV_SEPARATOR     = ',';
    const SCOPE_SEPARATOR   = '::';

    const CONFIG_KEY_AREA   = 'area';
    const CONFIG_KEY_LOCALE = 'locale';
    const CONFIG_KEY_STORE  = 'store';
    const CONFIG_KEY_DESIGN_PACKAGE = 'package';
    const CONFIG_KEY_DESIGN_THEME   = 'theme';

    /**
     * Locale name
     *
     * @var string
     */
    protected $_locale;

    /**
     * Translation object
     *
     * @var Zend_Translate_Adapter
     */
    protected $_translate;

    /**
     * Translator configuration array
     *
     * @var array
     */
    protected $_config;

    protected $_useCache = true;

    /**
     * Cache identifier
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Translation data
     *
     * @var array
     */
    protected $_data;

    /**
     * Translation data for data scope (per module)
     *
     * @var array
     */
    protected $_dataScope;

    public function __construct()
    {

    }

    /**
     * Initialization translation data
     *
     * @param   string $area
     * @return  Mage_Core_Model_Translate
     */
    public function init($area)
    {
        $this->setConfig(array(self::CONFIG_KEY_AREA=>$area));
        if ($this->_data = $this->_loadCache()) {
            if ($this->_canUseCache()) {
                return $this;
            }
            Mage::app()->removeCache($this->getCacheId());
        }

        $this->_data = array();

        foreach ($this->getModulesConfig() as $moduleName=>$info) {
            $info = $info->asArray();
            $this->_loadModuleTranslation($moduleName, $info['files']);
        }

        $this->_loadThemeTranslation();
        $this->_loadDbTranslation();
        $this->_saveCache();
        return $this;
    }

    /**
     * Retrieve modules configuration by translation
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getModulesConfig()
    {
        if (!Mage::getConfig()->getNode($this->getConfig(self::CONFIG_KEY_AREA).'/translate/modules')) {
            return array();
        }

        $config = Mage::getConfig()->getNode($this->getConfig(self::CONFIG_KEY_AREA).'/translate/modules')->children();
        if (!$config) {
            return array();
        }
        return $config;
    }

    /**
     * Initialize configuration
     *
     * @param   array $config
     * @return  Mage_Core_Model_Translate
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        if (!isset($this->_config[self::CONFIG_KEY_LOCALE])) {
            $this->_config[self::CONFIG_KEY_LOCALE] = $this->getLocale();
        }
        if (!isset($this->_config[self::CONFIG_KEY_STORE])) {
            $this->_config[self::CONFIG_KEY_STORE] = Mage::app()->getStore()->getId();
        }
        if (!isset($this->_config[self::CONFIG_KEY_DESIGN_PACKAGE])) {
            $this->_config[self::CONFIG_KEY_DESIGN_PACKAGE] = Mage::getDesign()->getPackageName();
        }
        if (!isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
            $this->_config[self::CONFIG_KEY_DESIGN_THEME] = Mage::getDesign()->getTheme('locale');
        }
        return $this;
    }

    /**
     * Retrieve config value by key
     *
     * @param   string $key
     * @return  mixed
     */
    public function getConfig($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }
        return null;
    }

    /**
     * Loading data from module translation files
     *
     * @param   string $moduleName
     * @param   string $files
     * @return  Mage_Core_Model_Translate
     */
    protected function _loadModuleTranslation($moduleName, $files)
    {
        foreach ($files as $file) {
            $file = $this->_getModuleFilePath($moduleName, $file);
            $this->_addData($this->_getFileData($file), $moduleName);
        }
        return $this;
    }

    /**
     * Adding translation data
     *
     * @param array $data
     * @param string $scope
     * @return Mage_Core_Model_Translate
     */
    protected function _addData($data, $scope)
    {
        foreach ($data as $key => $value) {
            $key    = $this->_prepareDataString($key);
            $value  = $this->_prepareDataString($value);
        	if ($scope && isset($this->_dataScope[$key])) {
        	    /**
        	     * Checking previos value
        	     */
        	    $scopeKey = $this->_dataScope[$key] . self::SCOPE_SEPARATOR . $key;
        	    if (!isset($this->_data[$scopeKey])) {
        	        if (isset($this->_data[$key])) {
        	            $this->_data[$scopeKey] = $this->_data[$key];
        	            unset($this->_data[$key]);
        	        }
        	    }
    	        $scopeKey = $scope . self::SCOPE_SEPARATOR . $key;
    	        $this->_data[$scopeKey] = $value;
        	}
        	else {
        	    $this->_data[$key]     = $value;
        	    $this->_dataScope[$key]= $scope;
        	}
        }
        return $this;
    }

    protected function _prepareDataString($string)
    {
        return str_replace('""', '"', trim($string, '"'));
    }

    /**
     * Loading current theme translation
     *
     * @return Mage_Core_Model_Translate
     */
    protected function _loadThemeTranslation()
    {
        $file = Mage::getDesign()->getLocaleFileName('translate.csv');
        $this->_addData($this->_getFileData($file), false);
        return $this;
    }

    /**
     * Loading current store translation from DB
     *
     * @return Mage_Core_Model_Translate
     */
    protected function _loadDbTranslation()
    {
        $this->_addData($this->getResource()->getTranslationArray(), $this->getConfig(self::CONFIG_KEY_STORE));
        return $this;
    }

    /**
     * Retrieve translation file for module
     *
     * @param   string $module
     * @return  string
     */
    protected function _getModuleFilePath($module, $fileName)
    {
        //$file = Mage::getConfig()->getModuleDir('locale', $module);
        $file = Mage::getBaseDir('locale');
        $file.= DS.$this->getLocale().DS.$fileName;
        return $file;
    }

    /**
     * Retrieve data from file
     *
     * @param   string $file
     * @return  array
     */
    protected function _getFileData($file)
    {
        $data = array();
        if (file_exists($file)) {
            $parser = new Varien_File_Csv();
            $parser->setDelimiter(self::CSV_SEPARATOR);
            $data = $parser->getDataPairs($file);
        }
        return $data;
    }

    /**
     * Retrieve translation data
     *
     * @return array
     */
    public function getData()
    {
        if (is_null($this->_data)) {
            return array();
            //Mage::throwException('Translation data is not initialized. Please contact developers.');
        }
        return $this->_data;
    }

    /**
     * Retrieve locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (is_null($this->_locale)) {
            $this->_locale = Mage::app()->getLocale()->getLocaleCode();
        }
        return $this->_locale;
    }

    /**
     * Retrieve DB resource model
     *
     * @return unknown
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('core/translate');
    }

    /**
     * Retrieve translation object
     *
     * @return Zend_Translate_Adapter
     */
    public function getTranslate()
    {
        if (is_null($this->_translate)) {
            $this->_translate = new Zend_Translate('array', $this->getData(), $this->getLocale());
        }
        return $this->_translate;
    }

    /**
     * Translate
     *
     * @param   array $args
     * @return  string
     */
    public function translate($args)
    {
        $text = array_shift($args);

        if ($text instanceof Mage_Core_Model_Translate_Expr) {
            $code = $text->getCode(self::SCOPE_SEPARATOR);
            $text = $text->getText();
            if (array_key_exists($code, $this->getData())) {
                $translated = $this->_data[$code];
            }
            elseif (array_key_exists($text, $this->getData())) {
            	$translated = $this->_data[$text];
            }
            else {
                $translated = $text;
            }
        }
        else {
            if (array_key_exists($text, $this->getData())) {
            	$translated = $this->_data[$text];
            }
            else {
                $translated = $text;
            }
        }

        array_unshift($args, $translated);

        $result = @call_user_func_array('sprintf', $args);
        if ($result === false){
            $result = $translated;
        }

        return $result;
    }

    /**
     * Retrieve cache identifier
     *
     * @return string
     */
    public function getCacheId()
    {
        if (is_null($this->_cacheId)) {
            $this->_cacheId = 'translate';
            if (isset($this->_config[self::CONFIG_KEY_LOCALE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_LOCALE];
            }
            if (isset($this->_config[self::CONFIG_KEY_AREA])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_AREA];
            }
            if (isset($this->_config[self::CONFIG_KEY_STORE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_STORE];
            }
            if (isset($this->_config[self::CONFIG_KEY_DESIGN_PACKAGE])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_DESIGN_PACKAGE];
            }
            if (isset($this->_config[self::CONFIG_KEY_DESIGN_THEME])) {
                $this->_cacheId.= '_'.$this->_config[self::CONFIG_KEY_DESIGN_THEME];
            }
        }
        return $this->_cacheId;
    }

    /**
     * Loading data cache
     *
     * @param   string $area
     * @return  array | false
     */
    protected function _loadCache()
    {
        if (!$this->_canUseCache()) {
            return false;
        }
        $data = Mage::app()->loadCache($this->getCacheId());
        $data = unserialize($data);
        return $data;
    }

    /**
     * Saving data cache
     *
     * @param   string $area
     * @return  Mage_Core_Model_Translate
     */
    protected function _saveCache()
    {
        if (!$this->_canUseCache()) {
            return $this;
        }
        Mage::app()->saveCache(serialize($this->getData()), $this->getCacheId(), array('translate'), null);
        return $this;
    }

    protected function _canUseCache()
    {
        //return $this->_useCache;
        return Mage::app()->useCache('translate');
    }
}
