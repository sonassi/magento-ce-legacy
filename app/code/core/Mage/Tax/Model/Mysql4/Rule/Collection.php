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
 * Tax rule collection
 *
 * @category   Mage
 * @package    Mage_Tax
 */

class Mage_Tax_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('tax/rule');
    }

    public function setClassTypeFilter($classType, $classTypeId)
    {
        $sqlJoin = $classType == 'CUSTOMER'
            ? 'main_table.tax_customer_class_id = class_table.class_id'
            : 'main_table.tax_product_class_id = class_table.class_id';

        $sqlWhere = $classType == 'CUSTOMER'
            ? 'main_table.tax_customer_class_id = ?'
            : 'main_table.tax_product_class_id = ?';

        $this->_sqlSelect->joinLeft(
            array('class_table' => $this->getTable('tax/tax_class')),
            $sqlJoin
        );
        $this->_sqlSelect->where($sqlWhere, $classTypeId);

        return $this;
    }

    public function joinClassTable()
    {
        $this->_sqlSelect->joinLeft(
            array('class_customer_table' => $this->getTable('tax/tax_class')),
            'main_table.tax_customer_class_id = class_customer_table.class_id',
            array('class_customer_name' => 'class_name')
        );
        $this->_sqlSelect->joinLeft(
            array('class_product_table' => $this->getTable('tax/tax_class')),
            'main_table.tax_product_class_id = class_product_table.class_id',
            array('class_product_name' => 'class_name')
        );

        return $this;
    }

    public function joinRateTypeTable()
    {
        $this->_sqlSelect->joinLeft(
            array('rate_type_table' => $this->getTable('tax/tax_rate_type')),
            'main_table.tax_rate_type_id = rate_type_table.type_id',
            array('rate_type_name' => 'type_name')
        );

        return $this;
    }
}