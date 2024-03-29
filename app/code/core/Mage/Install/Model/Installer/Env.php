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
 * @package    Mage_Install
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Environment installer
 *
 * @category   Mage
 * @package    Mage_Install
 */
class Mage_Install_Model_Installer_Env
{
    public function __construct() {}

    public function install()
    {
        if (!$this->_checkPhpExtensions()) {
            throw new Exception();
        }
        return $this;
    }

    protected function _checkPhpExtensions()
    {
        $res = true;
        $config = Mage::getSingleton('install/config')->getExtensionsForCheck();
        foreach ($config as $extension => $info) {
            if (!empty($info) && is_array($info)) {
                $res = $this->_checkExtension($info) && $res;
            }
            else {
                $res = $this->_checkExtension($extension) && $res;
            }
        }
        return $res;
    }

    protected function _checkExtension($extension)
    {
        if (is_array($extension)) {
            $oneLoaded = false;
            foreach ($extension as $item) {
                if (extension_loaded($item)) {
                    $oneLoaded = true;
                }
            }

            if (!$oneLoaded) {
                Mage::getSingleton('install/session')->addError(
                    Mage::helper('install')->__('One from PHP Extensions "%s" must be loaded', implode(',', $extension))
                );
                return false;
            }
        }
        elseif(!extension_loaded($extension)) {
                Mage::getSingleton('install/session')->addError(
                    Mage::helper('install')->__('PHP Extension "%s" must be loaded', $extension)
                );
            return false;
        }
        else {
            /*Mage::getSingleton('install/session')->addError(
                Mage::helper('install')->__("PHP Extension '%s' loaded", $extension)
            );*/
        }
        return true;
    }
}
