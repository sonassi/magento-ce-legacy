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


class Mage_Core_Model_Mysql4_Config extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('core/config_data', 'config_id');
    }

    /**
     * Get checksum for one or more tables
     *
     * @param string|array $tables string is separated by comma
     * @return integer|boolean
     */
    public function getChecksum($tables)
    {
        if (is_string($tables)) {
            $tablesArr = explode(',', $tables);
            $tables = array();
            foreach ($tablesArr as $table) {
                $table = $this->getTable(trim($table));
                if (!empty($table)) {
                    $tables[] = $table;
                }
            }
        }
        if (empty($tables) || !$this->_getReadAdapter()) {
            return false;
        }
        $checksumArr = $this->_getReadAdapter()->fetchAll('checksum table '.join(',', $tables));
        $checksum = 0;
        foreach ($checksumArr as $r) {
            $checksum += $r['Checksum'];
        }
        return $checksum;
    }

    /**
     * Load configuration values into xml config object
     *
     * @param Mage_Core_Model_Config $xmlConfig
     * @param string $cond
     * @return Mage_Core_Model_Mysql4_Config_Collection
     */
    public function loadToXml(Mage_Core_Model_Config $xmlConfig, $cond=null)
    {
        $read = $this->_getReadAdapter();
        if (!$read) {
            return $this;
        }

        #$tables = $read->fetchAll("show tables like 'core_%'");
        #print_r($tables);

        // initialize websites config
        $websites = array();
        $rows = $read->fetchAssoc("select website_id, code, name from ".$this->getTable('website'));
        foreach ($rows as $w) {
            $xmlConfig->setNode('websites/'.$w['code'].'/system/website/id', $w['website_id']);
            $xmlConfig->setNode('websites/'.$w['code'].'/system/website/name', $w['name']);
            $websites[$w['website_id']] = array('code'=>$w['code']);
        }

        // initialize stores config
        $stores = array();
        $rows = $read->fetchAssoc("select store_id, code, name, website_id from ".$this->getTable('store').' order by sort_order asc');
        foreach ($rows as $s) {
            $xmlConfig->setNode('stores/'.$s['code'].'/system/store/id', $s['store_id']);
            $xmlConfig->setNode('stores/'.$s['code'].'/system/store/name', $s['name']);
            $xmlConfig->setNode('stores/'.$s['code'].'/system/website/id', $s['website_id']);
            $xmlConfig->setNode('websites/'.$websites[$s['website_id']]['code'].'/system/stores/'.$s['code'], $s['store_id']);
            $stores[$s['store_id']] = array('code'=>$s['code']);
            $websites[$s['website_id']]['stores'][$s['store_id']] = $s['code'];
        }

        $subst_from = array();
        $subst_to = array();

        // get default distribution config vars
        /*
        $vars = Mage::getConfig()->getDistroServerVars();
        foreach ($vars as $k=>$v) {
            $subst_from[] = '{{'.$k.'}}';
            $subst_to[] = $v;
        }
        */

        // load all configuration records from database, which are not inherited
        $rows = $read->fetchAll("select scope, scope_id, path, value from ".$this->getMainTable().($cond ? ' where '.$cond : ''));

        // set default config values from database
        foreach ($rows as $r) {
            if ($r['scope']!=='default') {
                continue;
            }
            $value = str_replace($subst_from, $subst_to, $r['value']);
            $xmlConfig->setNode('default/'.$r['path'], $value);
        }

        // inherit default config values to all websites
        $extendSource = $xmlConfig->getNode('default');
        foreach ($websites as $id=>$w) {
            $xmlConfig->getNode('websites/'.$w['code'])->extend($extendSource);
        }

        // set websites config values from database
        foreach ($rows as $r) {
            if ($r['scope']!=='websites') {
                continue;
            }
            $value = str_replace($subst_from, $subst_to, $r['value']);
            $xmlConfig->setNode('websites/'.$websites[$r['scope_id']]['code'].'/'.$r['path'], $value);
        }

        // extend website config values to all associated stores
        foreach ($websites as $wId=>$website) {
            $extendSource = $xmlConfig->getNode('websites/'.$website['code']);
            if (isset($website['stores'])) {
                foreach ($website['stores'] as $sId=>$sCode) {
                    $xmlConfig->getNode('stores/'.$sCode)->extend($extendSource);
                }
            }
        }

        // set stores config values from database
        foreach ($rows as $r) {
            if ($r['scope']!=='stores') {
                continue;
            }
            $value = str_replace($subst_from, $subst_to, $r['value']);
            $xmlConfig->setNode('stores/'.$stores[$r['scope_id']]['code'].'/'.$r['path'], $value);
        }

#echo "<xmp>".$xmlConfig->getNode()->asNiceXml()."</xmp>"; exit;
        return $this;
    }
}