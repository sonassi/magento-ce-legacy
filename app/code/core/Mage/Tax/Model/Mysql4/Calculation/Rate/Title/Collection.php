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
 * Tax Rate Title Collection
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Mysql4_Calculation_Rate_Title_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('tax/calculation_rate_title', 'tax/calculation_rate_title');
    }

    public function loadByRateId($rateId)
    {
        $this->getSelect()->where('main_table.tax_calculation_rate_id = ?', $rateId);
        return $this->load();
    }
}