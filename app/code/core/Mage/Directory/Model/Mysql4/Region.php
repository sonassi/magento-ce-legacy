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
 * @package    Mage_Directory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Directory_Model_Mysql4_Region
{
    protected $_regionTable;
    protected $_regionNameTable;

    /**
     * DB read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * DB write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->_regionTable     = $resource->getTableName('directory/country_region');
        $this->_regionNameTable = $resource->getTableName('directory/country_region_name');
        $this->_read    = $resource->getConnection('directory_read');
        $this->_write   = $resource->getConnection('directory_write');
    }

    public function getIdFieldName()
    {
        return 'region_id';
    }

    public function load(Mage_Directory_Model_Region $region, $regionId)
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();

        $select = $this->_read->select()
            ->from(array('region'=>$this->_regionTable))
            ->where('region.region_id=?', $regionId)
            ->join(array('rname'=>$this->_regionNameTable), 
                'rname.region_id=region.region_id AND rname.locale=\''.$locale.'\'',
                array('name'));

        $region->setData($this->_read->fetchRow($select));
        return $this;
    }

    public function loadByCode(Mage_Directory_Model_Region $region, $regionCode, $countryId)
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();

        $select = $this->_read->select()
            ->from(array('region'=>$this->_regionTable))
            ->where('region.country_id=?', $countryId)
            ->where('region.code=?', $regionCode)
            ->join(array('rname'=>$this->_regionNameTable), 
                'rname.region_id=region.region_id AND rname.locale=\''.$locale.'\'',
                array('name'));

        $region->setData($this->_read->fetchRow($select));
        return $this;
    }
}
