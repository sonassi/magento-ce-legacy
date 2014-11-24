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
 * Customer saved payment info entity resource model
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Mage_Customer_Model_Entity_Payment extends Mage_Eav_Model_Entity_Abstract
{
    public function __construct() 
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('customer_payment')->setConnection(
            $resource->getConnection('customer_read'),
            $resource->getConnection('customer_write')
        );
    }
}