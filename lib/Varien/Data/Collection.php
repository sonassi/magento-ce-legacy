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
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data collection
 *
 * @category   Varien
 * @package    Varien_Data
 */
class Varien_Data_Collection implements IteratorAggregate, Countable
{
    const SORT_ORDER_ASC    = 'ASC';
    const SORT_ORDER_DESC   = 'DESC';

    /**
     * Collection items
     *
     * @var array
     */
    protected $_items = array();

    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = 'Varien_Object';

    /**
     * Order configuration
     *
     * @var array
     */
    protected $_orders = array();

    /**
     * Filters configuration
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * Filter rendered flag
     *
     * @var bool
     */
    protected $_isFiltersRendered = false;

    /**
     * Current page number for items pager
     *
     * @var int
     */
    protected $_curPage = 1;

    /**
     * Pager page size
     *
     * if page size is false, then we works with all items
     *
     * @var int || false
     */
    protected $_pageSize = false;

    /**
     * Total items number
     *
     * @var int
     */
    protected $_totalRecords = null;

    /**
     * Loading state flag
     *
     * @var bool
     */
    protected $_isCollectionLoaded;

    public function __construct()
    {

    }

    /**
     * Add collection filter
     *
     * @param string $field
     * @param string $value
     * @param string $type and|or|string
     */
    public function addFilter($field, $value, $type = 'and')
    {
        $filter = array();
        $filter['field']   = $field;
        $filter['value']   = $value;
        $filter['type']    = strtolower($type);

        $this->_filters[] = $filter;
        $this->_isFiltersRendered = false;
        return $this;
    }

    /**
     * Retrieve collection loading status
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->_isCollectionLoaded;
    }

    /**
     * Set collection loading status flag
     *
     * @param unknown_type $flag
     * @return unknown
     */
    protected function _setIsLoaded($flag = true)
    {
        $this->_isCollectionLoaded = $flag;
        return $this;
    }

    /**
     * Get current collection page
     *
     * @param  int $displacement
     * @return int
     */
    public function getCurPage($displacement = 0)
    {
        if ($this->_curPage + $displacement < 1) {
            return 1;
        }
        elseif ($this->_curPage + $displacement > $this->getLastPageNumber()) {
            return $this->getLastPageNumber();
        } else {
            return $this->_curPage + $displacement;
        }
    }

    /**
     * Retrieve collection last page number
     *
     * @return int
     */
    public function getLastPageNumber()
    {
        $collectionSize = (int) $this->getSize();
        if (0 === $collectionSize) {
            return 1;
        }
        elseif($this->_pageSize) {
            return ceil($collectionSize/$this->_pageSize);
        }
        else{
            return 1;
        }
    }

    /**
     * Retrieve collection page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * Retrieve collection all items count
     *
     * @return int
     */
    public function getSize()
    {
        $this->load();
        return $this->_totalRecords;
    }

    /**
     * Retrieve collection first item
     *
     * @return Varien_Object
     */
    public function getFirstItem()
    {
        $this->load();

        if (count($this->_items)) {
            reset($this->_items);
            return current($this->_items);
        }

        /*if(isset($this->_items[0])){
            return $this->_items[0];
        }*/
        return new $this->_itemObjectClass();
    }

    /**
     * Retrieve collection items
     *
     * @return array
     */
    public function getItems()
    {
        $this->load();
        return $this->_items;
    }

    /**
     * Retrieve field values from all items
     *
     * @param   string $colName
     * @return  array
     */
    public function getColumnValues($colName)
    {
        $this->load();

        $col = array();
        foreach ($this->getItems() as $item) {
            $col[] = $item->getData($colName);
        }
        return $col;
    }

    /**
     * Search all items by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  array
     */
    public function getItemsByColumnValue($column, $value)
    {
        $this->load();

        $res = array();
        foreach ($this as $item) {
        	if ($item->getData($column)==$value) {
        	    $res[] = $item;
        	}
        }
        return $res;
    }

    /**
     * Search first item by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  Varien_Object || null
     */
    public function getItemByColumnValue($column, $value)
    {
        $this->load();

        foreach ($this as $item) {
        	if ($item->getData($column)==$value) {
        	    return $item;
        	}
        }
        return null;
    }

    /**
     * Adding item to item array
     *
     * @param   Varien_Object $item
     * @return  Varien_Data_Collection
     */
    public function addItem(Varien_Object $item)
    {
        if ($item->getId()) {
            if (isset($this->_items[$item->getId()])) {
                throw new Exception('Item ('.get_class($item).') with the same id "'.$item->getId().'" already exist');
            }
            $this->_items[$item->getId()] = $item;
        }
        else {
            $this->_items[] = $item;
        }
        return $this;
    }

    /**
     * Remove item from collection by item key
     *
     * @param   mixed $key
     * @return  Varien_Data_Collection
     */
    public function removeItemByKey($key)
    {
        if (isset($this->_items[$key])) {
            unset($this->_items[$key]);
        }
        return $this;
    }

    /**
     * Clear collection
     *
     * @return Varien_Data_Collection
     */
    public function clear()
    {
        $this->_setIsLoaded(false);
        $this->_items = array();
        return $this;
    }

    /**
     * Walk all items of collection
     *
     * @param   string $method
     * @param   array $args
     * @return  Varien_Data_Collection
     */
    public function walk($method, $args=array())
    {
        foreach ($this as $item) {
            call_user_func_array(array($item, $method), $args);
        }
        return $this;
    }

    public function each($obj_method, $args=array())
    {
        foreach ($args->_items as $k => $item) {
            $args->_items[$k] = call_user_func($obj_method, $item);
        }
    }

    /**
     * Setting data for all collection items
     *
     * @param   mixed $key
     * @param   mixed $value
     * @return  Varien_Data_Collection
     */
    public function setDataToAll($key, $value=null)
    {
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->setDataToAll($k, $v);
            }
            return $this;
        }
        foreach ($this->getItems() as $item) {
            $item->setData($key, $value);
        }
        return $this;
    }

    /**
     * Set current page
     *
     * @param   int $page
     * @return  Varien_Data_Collection
     */
    public function setCurPage($page)
    {
        $this->_curPage = $page;
        return $this;
    }

    /**
     * Set collection page size
     *
     * @param   int $size
     * @return  Varien_Data_Collection
     */
    public function setPageSize($size)
    {
        $this->_pageSize = $size;
        return $this;
    }

    /**
     * Set select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  Varien_Data_Collection
     */
    public function setOrder($field, $direction = 'desc')
    {
        $this->_orders[$field] = $direction;
        return $this;
    }

    /**
     * Set collection item class name
     *
     * @param   string $className
     * @return  Varien_Data_Collection
     */
    function setItemObjectClass($className)
    {
        if (!is_subclass_of($className, 'Varien_Object')) {
            throw new Exception($className.' does not extends from Varien_Object');
        }
        $this->_itemObjectClass = $className;
        return $this;
    }

    /**
     * Render sql select conditions
     *
     * @return  Varien_Data_Collection
     */
    protected function _renderFilters()
    {
        return $this;
    }

    /**
     * Render sql select orders
     *
     * @return  Varien_Data_Collection
     */
    protected function _renderOrders()
    {
        return $this;
    }

    /**
     * Render sql select limit
     *
     * @return  Varien_Data_Collection
     */
    protected function _renderLimit()
    {
        return $this;
    }

    /**
     * Set select distinct
     *
     * @param bool $flag
     */
    public function distinct($flag)
    {
        return $this;
    }

    /**
     * Load data
     *
     * @return  Varien_Data_Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        return $this;
    }

    /**
     * Load data
     *
     * @return  Varien_Data_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        return $this->loadData($printQuery, $logQuery);
    }

    /**
     * Convert collection to XML
     *
     * @return string
     */
    public function toXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <collection>
           <totalRecords>'.$this->_totalRecords.'</totalRecords>
           <items>';

        foreach ($this as $item) {
            $xml.=$item->toXml();
        }
        $xml.= '</items>
        </collection>';
        return $xml;
    }

    /**
     * Convert collection to array
     *
     * @return array
     */
    public function toArray($arrRequiredFields = array())
    {
        $arrItems = array();
        $arrItems['totalRecords'] = $this->getSize();

        $arrItems['items'] = array();
        foreach ($this as $item) {
            $arrItems['items'][] = $item->toArray($arrRequiredFields);
        }
        return $arrItems;
    }

    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param   string $valueField
     * @param   string $labelField
     * @return  array
     */
    protected function _toOptionArray($valueField='id', $labelField='name', $additional=array())
    {
        $res = array();
        $additional['value'] = $valueField;
        $additional['label'] = $labelField;

        foreach ($this as $item) {
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
        	$res[] = $data;
        }
        return $res;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray();
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash();
    }

    /**
     * Convert items array to hash for select options
     *
     * return items hash
     * array($value => $label)
     *
     * @param   string $valueField
     * @param   string $labelField
     * @return  array
     */
    protected function _toOptionHash($valueField='id', $labelField='name')
    {
        $res = array();
        foreach ($this as $item) {
            $res[$item->getData($valueField)] = $item->getData($labelField);
        }
        return $res;
    }

    /**
     * Retrieve item by id
     *
     * @param   mixed $idValue
     * @return  Varien_Object
     */
    public function getItemById($idValue)
    {
        $this->load();
        if (isset($this->_items[$idValue])) {
            return $this->_items[$idValue];
        }
        return null;
    }

    /**
     * Implementation of IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        $this->load();
        return new ArrayIterator($this->_items);
    }

    /**
     * Retireve count of collection loaded items
     *
     * @return int
     */
    public function count()
    {
        $this->load();
        return count($this->_items);
    }
}