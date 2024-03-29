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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog layer filter abstract
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
abstract class Mage_Catalog_Block_Layer_Filter_Abstract extends Mage_Core_Block_Template
{
    protected $_filter;
    protected $_filterModelName;
    
    public function __construct() 
    {
        parent::__construct();
        $this->setTemplate('catalog/layer/filter.phtml');
    }

    public function init()
    {
        $this->_initFilter();
        return $this;
    }
    
    /**
     * Init filter model object
     *
     * @return Mage_Catalog_Block_Layer_Filter_Abstract
     */
    protected function _initFilter()
    {
        if (!$this->_filterModelName) {
            Mage::throwException(Mage::helper('catalog')->__('Filter model name must be declared'));
        }
        $this->_filter = Mage::getModel($this->_filterModelName);
        $this->_prepareFilter();
        
        $this->_filter->apply($this->getRequest(), $this);
        return $this;
    }
    
    protected function _prepareFilter()
    {
        return $this;
    }
    
    /**
     * Retrieve name of the filter block
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_filter->getName();
    }
    
    /**
     * Retrieve filter items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_filter->getItems();
    }
    
    /**
     * Retrieve filter items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->_filter->getItemsCount();
    }
    
    /**
     * Retrieve block html
     *
     * @return string
     */
    public function getHtml()
    {
        return parent::toHtml();
    }
}
