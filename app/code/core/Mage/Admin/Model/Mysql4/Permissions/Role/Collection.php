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
 * @package    Mage_Permissions
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

//class Mage_Permissions_Model_Mysql4_Role_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
class Mage_Admin_Model_Mysql4_Permissions_Role_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('admin/permissions_role');
    }

    /**
     * Enter description here...
     *
     * @param int $userId
     * @return Mage_Permissions_Model_Mysql4_Role_Collection
     */
    public function setUserFilter($userId)
    {
        $this->addFieldToFilter('user_id', $userId);
        $this->addFieldToFilter('role_type', 'G');
        return $this;
    }

    public function setRolesFilter()
    {
        $this->addFieldToFilter('role_type', 'G');
        return $this;
    }
}
