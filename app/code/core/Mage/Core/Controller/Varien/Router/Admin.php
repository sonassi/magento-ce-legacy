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


class Mage_Core_Controller_Varien_Router_Admin extends Mage_Core_Controller_Varien_Router_Standard
{
    public function fetchDefault()
    {
    	// set defaults
        $d = explode('/', Mage::getStoreConfig('web/default/admin'));
        $this->getFront()->setDefault(array(
            'module'     => !empty($d[0]) ? $d[0] : '',
            'controller' => !empty($d[1]) ? $d[1] : 'index',
            'action'     => !empty($d[2]) ? $d[2] : 'index'
        ));
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        $this->fetchDefault();

        $front = $this->getFront();

        $p = explode('/', trim($request->getPathInfo(), '/'));

        // get module name
        if ($request->getModuleName()) {
            $module = $request->getModuleName();
        } else {
            $p = explode('/', trim($request->getPathInfo(), '/'));
            $module = !empty($p[0]) ? $p[0] : $this->getFront()->getDefault('module');
        }
        if (!$module) {
            return false;
        }
        $realModule = $this->getModuleByFrontName($module);
        if (!$realModule) {
            if ($moduleFrontName = array_search($module, $this->_modules)) {
                $realModule = $module;
                $module = $moduleFrontName;
            } else {
                return false;
            }
        }

        if (!Mage::app()->isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        if (!$request->isPost()) {
            $shouldBeSecure = substr(Mage::getStoreConfig('web/unsecure/base_url'),0,5)==='https'
                || Mage::getStoreConfig('web/secure/use_in_adminhtml')
                && substr(Mage::getStoreConfig('web/secure/base_url'),0,5)==='https';

            if ($shouldBeSecure != Mage::app()->getStore()->isCurrentlySecure()) {
                $url = Mage::getBaseUrl('link', $shouldBeSecure).ltrim($request->getPathInfo(), '/');
#echo $url; exit;
                Mage::app()->getFrontController()->getResponse()
                    ->setRedirect($url)
                    ->sendResponse();
                exit;
            }
        }

        // get controller name
        if ($request->getControllerName()) {
            $controller = $request->getControllerName();
        } else {
            $controller = !empty($p[1]) ? $p[1] : $front->getDefault('controller');
        }
        $controllerFileName = $this->getControllerFileName($realModule, $controller);
        if (!$controllerFileName || !is_readable($controllerFileName)) {
            $controller = 'index';
            $action = 'noroute';
            $controllerFileName = $this->getControllerFileName($realModule, $controller);
        }

        $controllerClassName = $this->getControllerClassName($realModule, $controller);
        if (!$controllerClassName) {
            $controller = 'index';
            $action = 'noroute';
            $controllerFileName = $this->getControllerFileName($realModule, $controller);
        }

        // get action name
        if (empty($action)) {
            if ($request->getActionName()) {
                $action = $request->getActionName();
            } else {
                $action = !empty($p[2]) ? $p[2] : $front->getDefault('action');
            }
        }

        // include controller file if needed
        if (!class_exists($controllerClassName, false)) {
            include $controllerFileName;

            if (!class_exists($controllerClassName)) {
                throw Mage::exception('Mage_Core', Mage::helper('core')->__('Controller file was loaded but class does not exist'));
            }
        }
        // instantiate controller class
        $controllerInstance = new $controllerClassName($request, $front->getResponse());

        if (!$controllerInstance->hasAction($action)) {
            return false;
        }

        // set values only after all the checks are done
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);

        // set parameters from pathinfo
        for ($i=3, $l=sizeof($p); $i<$l; $i+=2) {
            $request->setParam($p[$i], isset($p[$i+1]) ? $p[$i+1] : '');
        }

        // dispatch action
        $request->setDispatched(true);
        $controllerInstance->dispatch($action);

        return true;#$request->isDispatched();
    }
}