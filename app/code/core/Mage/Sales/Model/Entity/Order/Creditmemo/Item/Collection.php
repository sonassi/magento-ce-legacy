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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Quote creditmemo items collection
 *
 * @category   Mage
 * @package    Mage_Sales
 */

class Mage_Sales_Model_Entity_Order_Creditmemo_Item_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    public function __construct()
    {
        $this->setEntity(Mage::getSingleton('sales_entity/order_creditmemo_item'));
        $this->setObject('sales/order_creditmemo_item');
    }

    public function setCreditmemoFilter($creditmemoId)
    {
        $this->addAttributeToFilter('parent_id', $creditmemoId);
        return $this;
    }
}