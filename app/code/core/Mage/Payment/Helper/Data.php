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
 * @package    Mage_Payment
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment module base helper
 *
 */
class Mage_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PAYMENT_METHODS = 'payment';

    /**
     * Retrieve method model object
     *
     * @param   string $code
     * @return  Mage_Payment_Model_Method_Abstract
     */
    public function getMethodInstance($code)
    {
        $key = self::XML_PATH_PAYMENT_METHODS.'/'.$code.'/model';
        $class = Mage::getStoreConfig($key);
        if (!$class) {
            Mage::throwException($this->__('Can not configuration for payment method with code: %s', $code));
        }
        return Mage::getModel($class);
    }

    /**
     * Retrieve available payment methods for store
     *
     * array structure:
     *  $index => Varien_Simplexml_Element
     *
     * @param   mixed $store
     * @return  array
     */
    public function getStoreMethods($store=null)
    {
        $methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, $store);
        $res = array();
        foreach ($methods as $code => $methodConfig) {
            $prefix = self::XML_PATH_PAYMENT_METHODS.'/'.$code.'/';

            if (!Mage::getStoreConfigFlag($prefix.'active')) {
                continue;
            }
            if (!$model = Mage::getStoreConfig($prefix.'model')) {
                continue;
            }

            $methodInstance = Mage::getModel($model);

            if ($methodInstance instanceof Mage_Payment_Model_Method_Cc && !$methodConfig->cctypes) {
                /* if the payment method has credit card types configuration option
                   and no credit card type is enabled in configuration */
                continue;
            }

            $sortOrder = (int)Mage::getStoreConfig($prefix.'sort_order');
            $methodInstance->sort_order=$sortOrder;
//            while (isset($res[$sortOrder])) {
//                $sortOrder++;
//            }
//            $res[$sortOrder] = $methodInstance;
            $res[] = $methodInstance;
        }
//        ksort($res);
        //die('!');
        
        //echo '<pre>';
        //var_dump( (array)$res);
        usort($res, array($this, '_sortMethods'));
        //var_dump((array)$res);
      //  echo '</pre>';
        return $res;
    }

    protected function _sortMethods($a, $b)
    {
       // var_dump($a);
        if (is_object($a)) {
            //var_dump($a->getData());
            //var_dump($a->sort_order);
            //die ();
            
            return (int)$a->sort_order < (int)$b->sort_order ? -1 : ((int)$a->sort_order > (int)$b->sort_order ? 1 : 0);
        }
        return 0;
    }

    /**
     * Retreive payment method form html
     *
     * @param   Mage_Payment_Model_Abstract $method
     * @return  Mage_Payment_Block_Form
     */
    public function getMethodFormBlock(Mage_Payment_Model_Method_Abstract $method)
    {
        $block = false;
        $blockType = $method->getFormBlockType();
        if ($this->getLayout()) {
            $block = $this->getLayout()->createBlock($blockType);
            $block->setMethod($method);
        }
        return $block;
    }

    /**
     * Retrieve payment information block
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Core_Block_Template
     */
    public function getInfoBlock(Mage_Payment_Model_Info $info)
    {
        $blockType = $info->getMethodInstance()->getInfoBlockType();
        if ($this->getLayout()) {
            $block = $this->getLayout()->createBlock($blockType);
        }
        else {
            $className = Mage::getConfig()->getBlockClassName($blockType);
            $block = new $className;
        }
        $block->setInfo($info);
        return $block;
    }
}