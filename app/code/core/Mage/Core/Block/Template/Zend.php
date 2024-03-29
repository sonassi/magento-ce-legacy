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
 * Zend html block 
 *
 * @category   Mage
 * @package    Mage_Core
 * @version    1.0 
 * @date       Thu Feb 08 05:56:43 EET 2007
 */

class Mage_Core_Block_Template_Zend extends Mage_Core_Block_Template
{
    protected $_view = null;
   
    /**
     * Class constructor. Base html block
     * 
     * @param      none
     * @return     void
     */
    function _construct()
    {
        parent::_construct();
        $this->_view = new Zend_View();
    }
    
    public function assign($key, $value=null)
    {
        if (is_array($key) && is_null($value)) {
            foreach ($key as $k=>$v) {
                $this->assign($k, $v);
            }
        } elseif (!is_null($value)) {
            $this->_view->assign($key, $value);
        }
        return $this;
    }
    
    public function setScriptPath($dir)
    {
        $this->_view->setScriptPath($dir.DS);
    }
    
    public function fetchView($fileName)
    {
        return $this->_view->render($fileName);
    }
}// Class Mage_Core_Block_Template END