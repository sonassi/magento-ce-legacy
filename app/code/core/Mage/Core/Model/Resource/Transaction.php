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

/**
 * Resource transaction model
 *
 * @todo need collect conection by name
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Resource_Transaction
{
    protected $_objects = array();
    protected $_objectsByAlias = array();
    protected $_resources = array();

    public function __construct()
    {

    }

    protected function _startTransaction()
    {
        foreach ($this->_objects as $object) {
        	$object->getResource()->beginTransaction();
        }
        return $this;
    }

    protected function _commitTransaction()
    {
        foreach ($this->_objects as $object) {
        	$object->getResource()->commit();
        }
        return $this;
    }

    protected function _rollbackTransaction()
    {
        foreach ($this->_objects as $object) {
        	$object->getResource()->rollBack();
        }
        return $this;
    }

    /**
     * Adding object for using in transaction
     *
     * @param   Mage_Core_Model_Abstract $object
     * @param   string $alias
     * @return  Mage_Core_Model_Resource_Transaction
     */
    public function addObject(Mage_Core_Model_Abstract $object, $alias='')
    {
        $this->_objects[] = $object;
        if (!empty($alias)) {
            $this->_objectsByAlias[$alias] = $object;
        }
        return $this;
    }

    /**
     * Initialize objects save transaction
     *
     * @return Mage_Core_Model_Resource_Transaction
     */
    public function save()
    {
        $this->_startTransaction();
        try {
            foreach ($this->_objects as $object) {
            	$object->save();
            }
            $this->_commitTransaction();
        }
        catch (Exception $e) {
            $this->_rollbackTransaction();
            throw $e;
        }
        return $this;
    }

    /**
     * Initialize objects delete transaction
     *
     * @return Mage_Core_Model_Resource_Transaction
     */
    public function delete()
    {
        $this->_startTransaction();
        try {
            foreach ($this->_objects as $object) {
            	$object->delete();
            }
            $this->_commitTransaction();
        }
        catch (Exception $e) {
            $this->_rollbackTransaction();
            throw $e;
        }
        return $this;
    }
}
