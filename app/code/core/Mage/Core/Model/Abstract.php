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
 * Abstract model class
 *
 * @category   Mage
 * @package    Mage_Core
 */
abstract class Mage_Core_Model_Abstract extends Varien_Object
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_abstract';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'object';

    /**
     * Name of the resource model
     *
     * @var string
     */
    protected $_resourceName;

    /**
     * Resource model instance
     *
     * @var Mage_Core_Model_Mysql4_Abstract
     */
    protected $_resource;

    /**
     * Name of the resource collection model
     *
     * @var string
     */
    protected $_resourceCollectionName;

    /**
     * Standard model initialization
     *
     * @param string $resourceModel
     * @param string $idFieldName
     * @return Mage_Core_Model_Abstract
     */
    protected function _init($resourceModel)
    {
        $this->_setResourceModel($resourceModel);
    }

    /**
     * Set resource names
     *
     * If collection name is ommited, resource name will be used with _collection appended
     *
     * @param string $resourceName
     * @param string|null $resourceCollectionName
     */
    protected function _setResourceModel($resourceName, $resourceCollectionName=null)
    {
        $this->_resourceName = $resourceName;
        if (is_null($resourceCollectionName)) {
            $resourceCollectionName = $resourceName.'_collection';
        }
        $this->_resourceCollectionName = $resourceCollectionName;
    }

    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _getResource()
    {
        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set'));
        }
        return Mage::getResourceSingleton($this->_resourceName);
    }


    /**
     * Retrieve identifier field name for model
     *
     * @return string
     */
    public function getIdFieldName()
    {
        if (!($fieldName = parent::getIdFieldName())) {
            $fieldName = $this->_getResource()->getIdFieldName();
            $this->setIdFieldName($fieldName);
        }
        return $fieldName;
    }

    /**
     * Retrieve model object identifier
     *
     * @return mixed
     */
    public function getId()
    {
        if ($this->getIdFieldName()) {
            return $this->getData($this->getIdFieldName());
        } else {
            return $this->getData('id');
        }
    }

    /**
     * Declare model object identifier value
     *
     * @param   mixed $id
     * @return  Mage_Core_Model_Abstract
     */
    public function setId($id)
    {
        if ($this->getIdFieldName()) {
            $this->setData($this->getIdFieldName(), $id);
        } else {
            $this->setData('id', $id);
        }
        return $this;
    }

    /**
     * Retrieve model resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resourceName;
    }

    /**
     * Get collection instance
     *
     * @return object
     */
    public function getResourceCollection()
    {
        if (empty($this->_resourceCollectionName)) {
            Mage::throwException(Mage::helper('core')->__('Model collection resource name is not defined'));
        }
        return Mage::getResourceModel($this->_resourceCollectionName, $this->_getResource());
    }

    public function getCollection()
    {
        return $this->getResourceCollection();
    }

    /**
     * Load object data
     *
     * @param   integer $id
     * @return  Mage_Core_Model_Abstract
     */
    public function load($id, $field=null)
    {
        $this->_getResource()->load($this, $id, $field);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        Mage::dispatchEvent('model_load_after', array('object'=>$this));
        Mage::dispatchEvent($this->_eventPrefix.'_load_after', array($this->_eventObject=>$this));
        return $this;
    }


    /**
     * Save object data
     *
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        $this->_getResource()->beginTransaction();
        try {
            $this->_beforeSave();
            $this->_getResource()->save($this);
            $this->_afterSave();

            $this->_getResource()->commit();
        }
        catch (Exception $e){
            $this->_getResource()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        Mage::dispatchEvent('model_save_before', array('object'=>$this));
        Mage::dispatchEvent($this->_eventPrefix.'_save_before', array($this->_eventObject=>$this));
        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        Mage::dispatchEvent('model_save_after', array('object'=>$this));
        Mage::dispatchEvent($this->_eventPrefix.'_save_after', array($this->_eventObject=>$this));
        return $this;
    }

    /**
     * Delete object from database
     *
     * @return Mage_Core_Model_Abstract
     */
    public function delete()
    {
        $this->_getResource()->beginTransaction();
        try {
            $this->_beforeDelete();
            $this->_getResource()->delete($this);
            $this->_afterDelete();

            $this->_getResource()->commit();
        }
        catch (Exception $e){
            $this->_getResource()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Processing object before delete data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeDelete()
    {
        Mage::dispatchEvent('model_delete_before', array('object'=>$this));
        Mage::dispatchEvent($this->_eventPrefix.'_delete_before', array($this->_eventObject=>$this));
        return $this;
    }

    /**
     * Processing object after delete data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterDelete()
    {
        Mage::dispatchEvent('model_delete_after', array('object'=>$this));
        Mage::dispatchEvent($this->_eventPrefix.'_delete_after', array($this->_eventObject=>$this));
        return $this;
    }

    /**
     * Retrieve model resource
     */
    public function getResource()
    {
        return $this->_getResource();
    }


}

