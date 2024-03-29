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
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Address abstract model
 *
 */
class Mage_Customer_Model_Address_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Directory country models
     *
     * @var array
     */
    static protected $_countryModels = array();

    /**
     * Directory region models
     *
     * @var array
     */
    static protected $_regionModels = array();

    public function getName()
    {
    	return $this->getFirstname().' '.$this->getLastname();
    }

    /**
     * get address street
     *
     * @param   int $line address line index
     * @return  string
     */
    public function getStreet($line=0)
    {
        $street = parent::getData('street');
        if (-1===$line) {
            return $street;
        } else {
            $arr = is_array($street) ? $street : explode("\n", $street);
            if (0===$line) {
                return $arr;
            } elseif (isset($arr[$line-1])) {
                return $arr[$line-1];
            } else {
                return '';
            }
        }
    }

    /**
     * set address street informa
     *
     * @param unknown_type $street
     * @return unknown
     */
    public function setStreet($street)
    {
        if (is_array($street)) {
            $street = trim(implode("\n", $street));
        }
        $this->setData('street', $street);
        return $this;
    }

    /**
     * get address data
     *
     * @param   string $key
     * @param   int $index
     * @return  mixed
     */
    public function getData($key='', $index=null)
    {
        if (strncmp($key, 'street', 6)) {
            $index = substr($key, 6);
            if (!is_numeric($index)) {
                $index = null;
            }
        }
        return parent::getData($key, $index);
    }

    /**
     * Create fields street1, street2, etc.
     *
     * To be used in controllers for views data
     *
     */
    public function explodeStreetAddress()
    {
        $streetLines = $this->getStreet();
        foreach ($streetLines as $i=>$line) {
            $this->setData('street'.($i+1), $line);
        }
        return $this;
    }

    /**
     * To be used when processing _POST
     */
    public function implodeStreetAddress()
    {
        $this->setStreet($this->getData('street'));
        return $this;
    }

    /**
     * Retrieve region name
     *
     * @return string
     */
    public function getRegion()
    {
        $regionId = $this->getData('region_id');
        $region   = $this->getData('region');

        if ($regionId) {
       	    if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
       	        $region = $this->getRegionModel($regionId)->getName();
    	        $this->setData('region', $region);
    	    }
        }

        if (!empty($region) && is_string($region)) {
    	    $this->setData('region', $region);
    	}
        elseif (!$regionId && is_numeric($region)) {
            if ($this->getRegionModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setData('region', $this->getRegionModel($region)->getName());
                $this->setData('region_id', $region);
            }
        }
    	elseif ($regionId && !$region) {
       	    if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
    	        $this->setData('region', $this->getRegionModel($regionId)->getName());
    	    }
    	}

    	return $this->getData('region');
    }

    /**
     * Return 2 letter state code if available, otherwise full region name
     *
     */
    public function getRegionCode()
    {
        $regionId = $this->getData('region_id');
        $region   = $this->getData('region');

        if (!$regionId && is_numeric($region)) {
            if ($this->getRegionModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setData('region_code', $this->getRegionModel($region)->getCode());
            }
        }
    	elseif ($regionId) {
    	    if ($this->getRegionModel($regionId)->getCountryId() == $this->getCountryId()) {
    	        $this->setData('region_code', $this->getRegionModel($regionId)->getCode());
    	    }
    	}
        elseif (is_string($region)) {
    	    $this->setData('region_code', $region);
    	}
    	return $this->getData('region_code');
    }

    public function getCountry()
    {
    	/*if ($this->getData('country_id') && !$this->getData('country')) {
    		$this->setData('country', Mage::getModel('directory/country')->load($this->getData('country_id'))->getIso2Code());
    	}
    	return $this->getData('country');*/
    	$country = $this->getCountryId();
    	return $country ? $country : $this->getData('country');
    }

    /**
     * Retrive country model
     *
     * @return Mage_Directory_Model_Country
     */
    public function getCountryModel()
    {
        if(!isset(self::$_countryModels[$this->getCountryId()])) {
            self::$_countryModels[$this->getCountryId()] = Mage::getModel('directory/country')->load($this->getCountryId());
        }

        return self::$_countryModels[$this->getCountryId()];
    }

    /**
     * Retrive country model
     *
     * @return Mage_Directory_Model_Country
     */
    public function getRegionModel($region=null)
    {
        if(is_null($region)) {
            $region = $this->getRegionId();
        }

        if(!isset(self::$_regionModels[$region])) {
            self::$_regionModels[$region] = Mage::getModel('directory/region')->load($region);
        }

        return self::$_regionModels[$region];
    }

    public function getHtmlFormat()
    {
        return "{{firstname}} {{lastname}}<br/>
            {{street}}<br/>
            {{city}}, {{regionName}} {{postcode}}<br/>
            T: {{telephone}}";
    }

    public function getFormated($html=false)
    {
    	return $this->format($html ? 'html' : 'text');//Mage::getModel('directory/country')->load($this->getCountryId())->formatAddress($this, $html);
    }

    public function format($type)
    {
        if(!($formatType = $this->getConfig()->getFormatByCode($type))
            || !$formatType->getRenderer()) {
            return null;
        }

    	return $formatType->getRenderer()->render($this);
    }

    /**
     * Retrive address config object
     *
     * @return Mage_Customer_Model_Address_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('customer/address_config');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->getRegion();
        return $this;
    }

}
