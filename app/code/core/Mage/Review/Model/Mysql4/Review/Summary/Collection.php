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
 * @package    Mage_Review
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review summery collection
 *
 * @category   Mage
 * @package    Mage_Review
 */
class Mage_Review_Model_Mysql4_Review_Summary_Collection extends Varien_Data_Collection_Db
{
    protected $_summaryTable;

    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');
        $this->_setIdFieldName('primary_id');
        parent::__construct($resources->getConnection('review_read'));
        $this->_summaryTable = $resources->getTableName('review/review_aggregate');

        $this->_sqlSelect->from($this->_summaryTable);
    }

    public function addEntityFilter($entityId, $entityType=1)
    {
        $this->_sqlSelect->where('entity_pk_value IN(?)', $entityId)
            ->where('entity_type = ?', $entityType);
        return $this;
    }

    public function addStoreFilter($storeId)
    {
        $this->_sqlSelect->where('store_id = ?', $storeId);
        return $this;
    }
}