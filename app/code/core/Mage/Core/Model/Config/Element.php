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


class Mage_Core_Model_Config_Element extends Varien_Simplexml_Element
{
    public function is($var, $value=true)
    {
        $flag = $this->$var;

        if ($value===true) {
            $flag = strtolower((string)$flag);
            if (!empty($flag) && 'false'!==$flag && '0'!==$flag) {
                return true;
            } else {
                return false;
            }
        }

        return !empty($flag) && (0===strcasecmp($value, (string)$flag));
    }

    public function getClassName()
    {
        if ($this->class) {
            $model = (string)$this->class;
        } elseif ($this->model) {
            $model = (string)$this->model;
        } else {
            return false;
        }
        return Mage::getConfig()->getModelClassName($model);
    }

}