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


class Mage_Core_Model_Design_Package
{
    const DEFAULT_AREA      = 'frontend';
    const DEFAULT_PACKAGE   = 'default';
    const DEFAULT_THEME     = 'default';
    const FALLBACK_THEME    = 'default';

    /**
     * Current Store for generation ofr base_dir and base_url
     *
     * @var string|integer|Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Package area
     *
     * @var string
     */
	protected $_area;

	/**
	 * Package name
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Package theme
	 *
	 * @var string
	 */
	protected $_theme;

	/**
	 * Package root directory
	 *
	 * @var string
	 */
	protected $_rootDir;

	protected $_config = null;

	public function __construct()
	{

	}

	/**
	 * Set store
	 *
	 * @param  string|integer|Mage_Core_Model_Store $store
	 * @return Mage_Core_Model_Design_Package
	 */
	public function setStore($store)
	{
		$this->_store = $store;
		return $this;
	}

	/**
	 * Retrieve store
	 *
	 * @return string|integer|Mage_Core_Model_Store
	 */
	public function getStore()
	{
		return $this->_store;
	}

	/**
	 * Set package area
	 *
	 * @param  string $area
	 * @return Mage_Core_Model_Design_Package
	 */
	public function setArea($area)
	{
		$this->_area = $area;
		return $this;
	}

	/**
	 * Retrieve package area
	 *
	 * @return unknown
	 */
	public function getArea()
	{
		if (is_null($this->_area)) {
			$this->_area = self::DEFAULT_AREA;
		}
		return $this->_area;
	}

	/**
	 * Set package name
	 *
	 * @param  string $name
	 * @return Mage_Core_Model_Design_Package
	 */
	public function setPackageName($name)
	{
		$this->_name = $name;
		return $this;
	}

	/**
	 * Retrieve package name
	 *
	 * @return string
	 */
	public function getPackageName()
	{
		if (is_null($this->_name)) {
			$this->_name = Mage::getStoreConfig('design/package/name');
			if (empty($this->_name)) {
				$this->_name = self::DEFAULT_PACKAGE;
			}
		}
		return $this->_name;
	}

	/**
	 * Declare design package theme params
	 *
	 * @return Mage_Core_Model_Design_Package
	 */
	public function setTheme()
	{
	    switch (func_num_args()) {
	        case 1:
    			foreach (array('layout', 'template', 'skin', 'locale') as $type) {
    				$this->_theme[$type] = func_get_arg(0);
    			}
    			break;

	        case 2:
			    $this->_theme[func_get_arg(0)] = func_get_arg(1);
			    break;

	        default:
	            throw Mage::exception(Mage::helper('core')->__('Wrong number of arguments for %s', __METHOD__));
		}
		return $this;
	}

	public function getTheme($type)
	{
		if (empty($this->_theme[$type])) {
			$this->_theme[$type] = Mage::getStoreConfig('design/theme/'.$type);
			if ($type!=='default' && empty($this->_theme[$type])) {
				$this->_theme[$type] = $this->getTheme('default');
				if (empty($this->_theme[$type])) {
					$this->_theme[$type] = self::DEFAULT_THEME;
				}
			}
		}
		return $this->_theme[$type];
	}

	public function getDefaultTheme()
	{
		return self::DEFAULT_THEME;
	}

	public function updateParamDefaults(array &$params)
	{
	    if ($this->getStore()) {
	        $params['_store'] = $this->getStore();
	    }
		if (empty($params['_area'])) {
			$params['_area'] = $this->getArea();
		}
		if (empty($params['_package'])) {
			$params['_package'] = $this->getPackageName();
		}
		if (empty($params['_theme'])) {
			$params['_theme'] = $this->getTheme($params['_type']);
		}
		return $this;
	}

	public function getBaseDir(array $params)
	{
		$this->updateParamDefaults($params);
		$baseDir = (empty($params['_relative']) ? Mage::getBaseDir('design').DS : '').
			$params['_area'].DS.$params['_package'].DS.$params['_theme'].DS.$params['_type'];
		return $baseDir;
	}

	public function getSkinBaseDir(array $params=array())
	{
		$this->updateParamDefaults($params);
		$baseDir = (empty($params['_relative']) ? Mage::getBaseDir('skin').DS : '').
			$params['_area'].DS.$params['_package'].DS.$params['_theme'];
		return $baseDir;
	}

	public function getLocaleBaseDir(array $params=array())
	{
		$this->updateParamDefaults($params);
		$baseDir = (empty($params['_relative']) ? Mage::getBaseDir('design').DS : '').
			$params['_area'].DS.$params['_package'].DS.$params['_theme'] . DS . 'locale' . DS .
			Mage::app()->getLocale()->getLocaleCode();
		return $baseDir;
	}

	public function getSkinBaseUrl(array $params=array())
	{
		$this->updateParamDefaults($params);
		$baseUrl = Mage::getBaseUrl('skin', isset($params['_secure'])?(bool)$params['_secure']:null)
			.$params['_area'].'/'.$params['_package'].'/'.$params['_theme'].'/';
		return $baseUrl;
	}

	/**
     * Get absolute file path for requested file or false if doesn't exist
     *
     * Possible params:
     * - _type:
     * 	 - layout
     *   - template
     *   - skin
     *   - translate
     * - _package: design package, if not set = default
     * - _theme: if not set = default
     * - _file: path relative to theme root
     *
     * @see Mage_Core_Model_Config::getBaseDir
     * @param string $file
     * @param array $params
     * @return string|boolean
     *
     */
    public function validateFile($file, array $params)
    {
    	Varien_Profiler::start(__METHOD__);
    	switch ($params['_type']) {
    		case 'skin':
    			$fileName = $this->getSkinBaseDir($params);
    			break;

    		case 'locale':
    			$fileName = $this->getLocaleBasedir($params);
    			break;

    		default:
    			$fileName = $this->getBaseDir($params);
    			break;
    	}
    	$fileName.= DS.$file;

		$testFile = (empty($params['_relative']) ? '' : Mage::getBaseDir('design').DS) . $fileName;

		if ($this->getDefaultTheme()!==$params['_theme'] && !file_exists($testFile)) {
    		return false;
    	}
    	Varien_Profiler::stop(__METHOD__);
    	return $fileName;
    }

    /**
     * Use this one to get existing file name with fallback to default
     *
     * $params['_type'] is required
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getFilename($file, array $params)
    {
    	Varien_Profiler::start(__METHOD__);
    	$this->updateParamDefaults($params);
    	if (empty($params['_default'])) {
    		$params['_default'] = false;
    	}
		$filename = $this->validateFile($file, $params);
		if (false===$filename) {
			if ($this->getDefaultTheme()===$params['_theme']) {
				return $params['_default'];
			}
			$params['_theme'] = $this->getDefaultTheme();
			$filename = $this->validateFile($file, $params);
			if (false===$filename) {
				return $params['_default'];
			}
		}
		Varien_Profiler::stop(__METHOD__);
		return $filename;
    }

    public function getLayoutFilename($file, array $params=array())
    {
    	$params['_type'] = 'layout';
    	return $this->getFilename($file, $params);
    }

    public function getTemplateFilename($file, array $params=array())
    {
    	$params['_type'] = 'template';
    	return $this->getFilename($file, $params);
    }

    public function getLocaleFileName($file, array $params=array())
    {
        $params['_type'] = 'locale';
    	return $this->getFilename($file, $params);
    }

    /**
     * Get skin file url
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getSkinUrl($file=null, array $params=array())
    {
    	Varien_Profiler::start(__METHOD__);
    	if (empty($params['_type'])) {
    		$params['_type'] = 'skin';
    	}
    	if (empty($params['_default'])) {
    		$params['_default'] = false;
    	}
    	$this->updateParamDefaults($params);
    	if (!empty($file)) {
			$filename = $this->validateFile($file, $params);
			if (false===$filename) {
				if ($this->getDefaultTheme()===$params['_theme']) {
					return $params['_default'];
				}
				$params['_theme'] = $this->getDefaultTheme();
				$filename = $this->validateFile($file, $params);
				if (false===$filename) {
					return $params['_default'];
				}
			}
    	}

    	$url = $this->getSkinBaseUrl($params).(!empty($file) ? $file : '');
    	Varien_Profiler::stop(__METHOD__);
    	return $url;
    }

    public function getPackageList()
    {
        $directory = Mage::getBaseDir('design') . DS . 'frontend';
        return $this->_listDirectories($directory);
    }

    public function getThemeList($package = null)
    {
        $result = array();

        if (is_null($package)){
            foreach ($this->getPackageList() as $package){
                $result[$package] = $this->getThemeList($package);
            }
        } else {
            $directory = Mage::getBaseDir('design') . DS . 'frontend' . DS . $package;
            $result = $this->_listDirectories($directory);
        }

        return $result;
    }

    private function _listDirectories($path, $fullPath = false){
        $result = array();
        $dir = opendir($path);
        if ($dir) {
        	while ($entry = readdir($dir)) {
        		if (substr($entry, 0, 1) == '.' || !is_dir($path . DS . $entry)){
        		    continue;
        		}
        		if ($fullPath) {
        		    $entry = $path . DS . $entry;
        		}
        		$result[] = $entry;
        	}
        	unset($entry);
        	closedir($dir);
        }

        return $result;
    }
}
