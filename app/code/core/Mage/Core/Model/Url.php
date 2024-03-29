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
 * URL
 *
 * Properties:
 *
 * - request
 *
 * - relative_url: true, false
 * - type: 'link', 'skin', 'js', 'media'
 * - store: instanceof Mage_Core_Model_Store
 * - secure: true, false
 *
 * - scheme: 'http', 'https'
 * - user: 'user'
 * - password: 'password'
 * - host: 'localhost'
 * - port: 80, 443
 * - base_path: '/dev/magento'
 * - base_script: 'index.php/'
 *
 * - route_path: '/module/controller/action/param1/value1/param2/value2'
 * - route_name: 'module'
 * - controller_name: 'controller'
 * - action_name: 'action'
 * - route_params: array('param1'=>'value1', 'param2'=>'value2')
 *
 * - query: (?)'param1=value1&param2=value2'
 * - query_array: array('param1'=>'value1', 'param2'=>'value2')
 * - fragment: (#)'fragment-anchor'
 *
 * URL structure:
 *
 * https://user:password@host:443/base_path/[base_script]route_name/controller_name/action_name/param1/value1?query_param=query_value#fragment
 *       \__________A___________/\____________________________________B_____________________________________/
 * \__________________C___________________/              \__________________D_________________/ \_____E_____/
 * \_____________F______________/                        \___________________________G______________________/
 * \___________________________________________________H____________________________________________________/
 *
 * - A: authority
 * - B: path
 * - C: absolute_base_url
 * - D: action_path
 * - E: route_params
 * - F: host_url
 * - G: route_path
 * - H: route_url
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Url extends Varien_Object
{
    const DEFAULT_CONTROLLER_NAME   = 'index';
    const DEFAULT_ACTION_NAME       = 'index';

    const XML_PATH_UNSECURE_PROTOCOL= 'web/unsecure/protocol';
    const XML_PATH_UNSECURE_HOST    = 'web/unsecure/host';
    const XML_PATH_UNSECURE_PORT    = 'web/unsecure/port';
    const XML_PATH_UNSECURE_PATH    = 'web/unsecure/base_path';
    const XML_PATH_SECURE_PROTOCOL  = 'web/secure/protocol';
    const XML_PATH_SECURE_HOST      = 'web/secure/host';
    const XML_PATH_SECURE_PORT      = 'web/secure/port';
    const XML_PATH_SECURE_PATH      = 'web/secure/base_path';

    const XML_PATH_UNSECURE_URL     = 'web/unsecure/base_url';
    const XML_PATH_SECURE_URL       = 'web/secure/base_url';

    static protected $_configDataCache;
    static protected $_baseUrlCache;
    static protected $_encryptedSessionId;

    /**
     * Controller request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    protected function _construct()
    {
        $this->setStore(null);
    }

    /**
     * Initialize object data from retrieved url
     *
     * @param   string $url
     * @return  Mage_Core_Model_Url
     */
    public function parseUrl($url)
    {
        $data   = parse_url($url);
        $parts  = array(
            'scheme'=>'setScheme',
            'host'  =>'setHost',
            'port'  =>'setPort',
            'user'  =>'setUser',
            'pass'  =>'setPassword',
            'path'  =>'setPath',
            'query' =>'setQuery',
            'fragment'=>'setFragment');

        foreach ($parts as $component=>$method) {
            if (isset($data[$component])) {
                $this->$method($data[$component]);
            }
        }
        return $this;
    }

    /**
     * Retrieve default controller name
     *
     * @return string
     */
    public function getDefaultControllerName()
    {
        return self::DEFAULT_CONTROLLER_NAME;
    }

    /**
     * Retrieve default action name
     *
     * @return string
     */
    public function getDefaultActionName()
    {
        return self::DEFAULT_ACTION_NAME;
    }

    public function getConfigData($key, $prefix=null)
    {
//        if (!isset(self::$_configDataCache)) {
//           $this->loadCache();
//        }
        if (is_null($prefix)) {
            $prefix = 'web/'.($this->getSecure() ? 'secure' : 'unsecure').'/';
        }
        $path = $prefix.$key;

        $cacheId = $this->getStore()->getCode().'/'.$path;
        if (!isset(self::$_configDataCache[$cacheId])) {
            $data = $this->getStore()->getConfig($path);
            self::$_configDataCache[$cacheId] = $data;
        }

        return self::$_configDataCache[$cacheId];
    }

    public function setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Zend request object
     *
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = Mage::app()->getFrontController()->getRequest();
        }
        return $this->_request;
    }

    public function getType()
    {
        if (!$this->hasData('type')) {
            $this->setData('type', Mage_Core_Model_Store::URL_TYPE_LINK);
        }
        return $this->getData('type');
    }

    public function getSecure()
    {
        if (!$this->hasData('secure')) {
            if ($this->getType()===Mage_Core_Model_Store::URL_TYPE_LINK) {
                $this->setData('secure', Mage::getConfig()->shouldUrlBeSecure('/'.$this->getActionPath()));
            } else {
                $this->setData('secure', Mage::app()->getStore()->isCurrentlySecure());
            }
        }
        return $this->getData('secure');
    }

    public function setStore($data)
    {
        $this->setData('store', Mage::app()->getStore($data));
        return $this;
    }

    /**
     * Get current store for the url instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (!$this->hasData('store')) {
            $this->setStore(null);
        }
        return $this->getData('store');
    }

    public function getBaseUrl($params=array())
    {
        if (isset($params['_store'])) {
            $this->setStore($params['_store']);
        }
        if (isset($params['_type'])) {
            $this->setType($params['_type']);
        }
        if (isset($params['_secure'])) {
            $this->setSecure($params['_secure']);
        }

        return $this->getStore()->getBaseUrl($this->getType(), $this->getSecure());
    }

    public function setRoutePath($data)
    {
        if ($this->getData('route_path')==$data) {
            return $this;
        }

        $a = explode('/', $data);

        $route = array_shift($a);
        if ('*'===$route) {
            $frontName = $this->getRequest()->getModuleName();
            $router = Mage::app()->getFrontController()->getRouterByFrontName($frontName);
            $route = $router->getRouteByFrontName($frontName);
        }
        $this->setRouteName($route);
        $routePath = $route.'/';

        if (!empty($a)) {
            $controller = array_shift($a);
            if ('*'===$controller) {
                $controller = $this->getRequest()->getControllerName();
            }
            $this->setControllerName($controller);
            $routePath .= $controller.'/';
        }

        if (!empty($a)) {
            $action = array_shift($a);
            if ('*'===$action) {
                $action = $this->getRequest()->getActionName();
            }
            $this->setActionName($action);
            $routePath .= $action.'/';
        }

        if (!empty($a)) {
            $this->unsetData('route_params');
            while (!empty($a)) {
                $key = array_shift($a);
                if (!empty($a)) {
                    $value = array_shift($a);
                    $this->setRouteParam($key, $value);
                    #$routePath .= $key.'/'.urlencode($value).'/';
                    $routePath .= $key.'/'.$value.'/';
                }
            }
        }

        #$this->setData('route_path', $routePath);

        return $this;
    }

    public function getActionPath()
    {
        if (!$this->getRouteName()) {
            return '';
        }

        $hasParams = (bool)$this->getRouteParams();
        $path = $this->getRouteFrontName() . '/';

        if ($this->getControllerName()) {
            $path .= $this->getControllerName() . '/';
        } elseif ($hasParams) {
            $path .= $this->getDefaultControllerName() . '/';
        }
        if ($this->getActionName()) {
            $path .= $this->getActionName() . '/';
        } elseif ($hasParams) {
            $path .= $this->getDefaultActionName() . '/';
        }

        return $path;
    }

    public function getRoutePath()
    {
        if (!$this->hasData('route_path')) {
            $routePath = $this->getActionPath();
            if ($this->getRouteParams()) {
                foreach ($this->getRouteParams() as $key=>$value) {
                    if (is_null($value) || false===$value || ''===$value || !is_scalar($value)) {
                        continue;
                    }
                    #$routePath .= $key.'/'.urlencode($value).'/';
                    $routePath .= $key.'/'.$value.'/';
                }
            }
            if ($routePath != '' && substr($routePath, -1, 1) !== '/') {
                $routePath.= '/';
            }
            $this->setData('route_path', $routePath);
        }
        return $this->getData('route_path');
    }

    public function setRouteName($data)
    {
        if ($this->getData('route_name')==$data) {
            return $this;
        }
        $this->unsetData('route_front_name')
            ->unsetData('route_path')
            ->unsetData('controller_name')
            ->unsetData('action_name')
            ->unsetData('secure');
        return $this->setData('route_name', $data);
    }

    public function getRouteFrontName()
    {
        if (!$this->hasData('route_front_name')) {
            $routeName = $this->getRouteName();
            $route = Mage::app()->getFrontController()->getRouterByRoute($routeName);
            $frontName = $route->getFrontNameByRoute($routeName);

            $this->setRouteFrontName($frontName);
        }

        return $this->getData('route_front_name');
    }

    public function getRouteName()
    {
        return $this->getData('route_name');
    }

    public function setControllerName($data)
    {
        if ($this->getData('controller_name')==$data) {
            return $this;
        }
        $this->unsetData('route_path')->unsetData('action_name')->unsetData('secure');
        return $this->setData('controller_name', $data);
    }

    public function getControllerName()
    {
        return $this->getData('controller_name');
    }

    public function setActionName($data)
    {
        if ($this->getData('action_name')==$data) {
            return $this;
        }
        $this->unsetData('route_path');
        return $this->setData('action_name', $data)->unsetData('secure');
    }

    public function getActionName()
    {
        return $this->getData('action_name');
    }

    public function setRouteParams(array $data, $unsetOldParams=true)
    {
        if (isset($data['_type'])) {
            $this->setType($data['_type']);
            unset($data['_type']);
        }

        if (isset($data['_secure'])) {
            $this->setSecure((bool)$data['_secure']);
            unset($data['_secure']);
        }

        if (isset($data['_absolute'])) {
            unset($data['_absolute']);
        }

        if ($unsetOldParams) {
            $this->unsetData('route_params');
        }

        $this->setUseUrlCache(true);
        if (isset($data['_current'])) {
            if (is_array($data['_current'])) {
                foreach ($data['_current'] as $key) {
                    if (array_key_exists($key, $data) || !$this->getRequest()->getUserParam($key)) {
                        continue;
                    }
                    $data[$key] = $this->getRequest()->getUserParam($key);
                }
            } elseif ($data['_current']) {
                foreach ($this->getRequest()->getUserParams() as $key=>$value) {
                    if (array_key_exists($key, $data) || $this->getRouteParam($key)) {
                        continue;
                    }
                    $data[$key] = $value;
                }
                foreach ($this->getRequest()->getQuery() as $key=>$value) {
                    $this->setQueryParam($key, $value);
                }
                $this->setUseUrlCache(false);
            }
            unset($data['_current']);
        }

        foreach ($data as $k=>$v) {
            $this->setRouteParam($k, $v);
        }

        return $this;
    }

    public function getRouteParams()
    {
        return $this->getData('route_params');
    }

    public function setRouteParam($key, $data)
    {
        $params = $this->getData('route_params');
        if (isset($params[$key]) && $params[$key]==$data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('route_path');
        return $this->setData('route_params', $params);
    }

    public function getRouteParam($key)
    {
        return $this->getData('route_params', $key);
    }

    public function getRouteUrl($routePath=null, $routeParams=null)
    {
        $this->unsetData('route_params');

        if (!is_null($routePath)) {
            $this->setRoutePath($routePath);
        }
        if (is_array($routeParams)) {
            $this->setRouteParams($routeParams, false);
        }

        $url = $this->getBaseUrl().$this->getRoutePath();
        return $url;
    }

    /**
     * If the host was switched but session cookie won't recognize it - add session id to query
     *
     * @return unknown
     */
    public function checkCookieDomains()
    {
        $hostArr = explode(':', $this->getRequest()->getServer('HTTP_HOST'));
        if ($hostArr[0]!==$this->getHost()) {
            $session = Mage::getSingleton('core/session');
            if (!$session->isValidForHost($this->getHost())) {
                if (!self::$_encryptedSessionId) {
                    $helper = Mage::helper('core');
                    if (!$helper) {
                        return $this;
                    }
                    self::$_encryptedSessionId = $helper->encrypt($session->getSessionId());
                }
                $this->setQueryParam(
                    Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM,
                    self::$_encryptedSessionId
                );
            }
        }
        return $this;
    }

    public function addSessionParam()
    {
        $session = Mage::getSingleton('core/session');

        if (!self::$_encryptedSessionId) {
            $helper = Mage::helper('core');
            if (!$helper) {
                return $this;
            }
            self::$_encryptedSessionId = $helper->encrypt($session->getSessionId());
        }
        $this->setQueryParam(
            Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM,
            self::$_encryptedSessionId
        );
        return $this;
    }


    public function setQuery($data)
    {
        if ($this->getData('query')==$data) {
            return $this;
        }
        $this->unsetData('query_params');
        return $this->setData('query', $data);
    }

    public function getQuery()
    {
        if (!$this->hasData('query')) {
            $query = '';
            if (is_array($this->getQueryParams())) {
                $query = http_build_query($this->getQueryParams());
            }
            $this->setData('query', $query);
        }
        return $this->getData('query');
    }

    public function setQueryParams(array $data)
    {
        if ($this->getData('query_params')==$data) {
            return $this;
        }
        $this->unsetData('query');
        return $this->setData('query_params', $data);
    }

    public function getQueryParams()
    {
        if (!$this->hasData('query_params')) {
            $params = array();
            if ($this->getData('query')) {
                foreach (explode('&', $this->getData('query')) as $param) {
                    $paramArr = explode('=', $param);
                    $params[$paramArr[0]] = urldecode($paramArr[1]);
                }
            }
            $this->setData('query_params', $params);
        }
        return $this->getData('query_params');
    }

    public function setQueryParam($key, $data)
    {
        $params = $this->getQueryParams();
        if (isset($params[$key]) && $params[$key]==$data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('query');
        return $this->setData('query_params', $params);
    }

    public function getQueryParam($key)
    {
        if (!$this->hasData('query_params')) {
            $this->getQueryParams();
        }
        return $this->getData('query_params', $key);
    }

    public function setFragment($data)
    {
        return $this->setData('fragment', $data);
    }

    public function getFragment()
    {
        return $this->getData('fragment');
    }

    public function getUrl($routePath=null, $routeParams=null)
    {
        Varien_Profiler::start(__METHOD__);

        $url = $this->getRouteUrl($routePath, $routeParams);

        $session = Mage::getSingleton('core/session');
        if ($sessionId = $session->getSessionIdForHost($url)) {
            $this->setQueryParam($session->getSessionIdQueryParam(), $sessionId);
        }

        if ($this->getQuery()) {
            $url .= '?'.$this->getQuery();
        }

        if ($this->getFragment()) {
            $url .= '#'.$this->getFragment();
        }
        Varien_Profiler::stop(__METHOD__);

        return $url;
    }
}