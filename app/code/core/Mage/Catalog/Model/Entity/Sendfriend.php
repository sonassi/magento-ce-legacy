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

class Mage_Catalog_Model_Entity_Sendfriend extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('catalog/sendfriend', 'log_id');
    }

    public function getSendCount($model, $ip, $startTime)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getTable('sendfriend')), new Zend_Db_Expr('count(*)'))
            ->where('main_table.ip = ?',  $ip)
            ->where('main_table.time >= ?', $startTime);

        $data = $this->_getReadAdapter()->fetchRow($select);

        return $data['count(*)'];
    }

	public function deleteLogsBefore($time)
	{
        $deleted = $this->_getWriteAdapter()
            ->delete($this->getTable('sendfriend'), $this->_getWriteAdapter()->quoteInto('time < ?', $time));

        return $this;
	}
}