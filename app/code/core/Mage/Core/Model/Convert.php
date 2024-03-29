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
 * Mage Core convert model
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Convert extends Varien_Convert_Profile_Collection
{
    public function __construct()
    {
        $classArr = explode('_', get_class($this));
        $moduleName = $classArr[0].'_'.$classArr[1];
        $etcDir = Mage::getConfig()->getModuleDir('etc', $moduleName);

        $fileName = $etcDir.DS.'convert.xml';
        if (is_readable($fileName)) {
            $data = file_get_contents($fileName);
            $this->importXml($data);
        }
    }

    public function getClassNameByType($type)
    {
        if (strpos($type, '/')!==false) {
            return Mage::getConfig()->getModelClassName($type);
        }
        return parent::getClassNameByType($type);
    }
}