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
 * @package    Mage_Tax
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Enter description here...
 *
 * attributes:
 * - customer_class_id
 * - product_class_id
 * - region_id
 * - county_id
 * - postcode
 */
class Mage_Tax_Model_Rate_Data extends Mage_Core_Model_Abstract
{
    protected $_cache = array();

    protected function _construct()
    {
        $this->_init('tax/rate_data');
    }

    public function getRate()
    {
        if (!$this->getPostcode()
            || !$this->getRegionId()
            //|| !$this->getCustomerClassId()
            //|| !$this->getProductClassId()
            ) {
            return 0;
            #throw Mage::exception('Mage_Tax', Mage::helper('tax')->__('Invalid data for tax rate calculation'));
        }

        $cacheKey = $this->getCustomerClassId()
            .'|'.$this->getProductClassId()
            .'|'.$this->getRegionId()
            .'|'.$this->getPostcode()
            .'|'.$this->getCountyId();

        if (!isset($this->_cache[$cacheKey])) {
            $this->_cache[$cacheKey] = $this->_getResource()->fetchRate($this);
        }

        return $this->_cache[$cacheKey];
    }

    public function getRegionId()
    {
        if ($this->getPostcode()) {
            $regionId = Mage::getModel('usa/postcode')->load($this->getPostcode())->getRegionId();
            if ($regionId) {
                $this->setRegionId($regionId);
            }
        }
        return $this->getData('region_id');
    }

    public function getCustomerClassId()
    {
        if (!$this->getData('customer_class_id')) {
            $this->setCustomerClassId(Mage::getSingleton('customer/session')->getCustomer()->getTaxClassId());
        }
        return $this->getData('customer_class_id');
    }
}
