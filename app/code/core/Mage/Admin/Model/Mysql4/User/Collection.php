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
 * @package    Mage_Admin
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Users collection
 *
 * @category   Mage
 * @package    Mage_Admin
 */
class Mage_Admin_Model_Mysql4_User_Collection extends Varien_Data_Collection_Db
{
    protected $_userTable;
    
    public function __construct() 
    {
        parent::__construct(Mage::getSingleton('core/resource')->getConnection('admin_read'));
        $this->_userTable = Mage::getSingleton('core/resource')->getTableName('admin/user');
        $this->_sqlSelect->from($this->_userTable);
        
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('admin/user'));
    }
}