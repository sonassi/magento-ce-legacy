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
 * Base items collection class
 *
 * @category   Varien
 * @package    Varien_Data
 */
class Varien_Data_Collection_Db extends Varien_Data_Collection
{
    /**
     * DB connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_conn;

    /**
     * Select oblect
     *
     * @var Zend_Db_Select
     */
    protected $_sqlSelect;

    /**
     * Identifier fild name for collection items
     *
     * Can be used by collections with items without defined
     *
     * @var string
     */
    protected $_idFieldName;

    public function __construct($conn=null)
    {
        parent::__construct();

        if (!is_null($conn)) {
            $this->setConnection($conn);
        }
    }

    protected function _setIdFieldName($fieldName)
    {
        $this->_idFieldName = $fieldName;
        return $this;
    }

    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    public function setConnection($conn)
    {
        if (!$conn instanceof Zend_Db_Adapter_Abstract) {
            throw new Zend_Exception('dbModel read resource does not implement Zend_Db_Adapter_Abstract');
        }

        $this->_conn = $conn;
        $this->_sqlSelect = $this->_conn->select();
    }

    /**
     * Get Zend_Db_Select instance
     *
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        return $this->_sqlSelect;
    }

    /**
     * Retrieve connection object
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getConnection()
    {
        return $this->_conn;
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = $this->_conn->fetchOne($sql);
        }
        return intval($this->_totalRecords);
    }

    /**
     * Get sql for get record count
     *
     * @return  string
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->_sqlSelect;
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        $sql = $countSelect->__toString();
        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(*) from ', $sql);
        return $sql;
    }

    /**
     * Get sql select string or object
     *
     * @param   bool $stringMode
     * @return  string || Zend_Db_Select
     */
    function getSelectSql($stringMode = false)
    {
        if ($stringMode) {
            return $this->_sqlSelect->__toString();
        }
        return $this->_sqlSelect;
    }


    /**
     * Set select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  Varien_Data_Collection_Db
     */
    public function setOrder($field, $direction = 'desc')
    {
        $direction = (strtoupper($direction)==self::SORT_ORDER_ASC) ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;
        $this->_orders[$field] = new Zend_Db_Expr($field.' '.$direction);
        return $this;
    }

    /**
     * Render sql select conditions
     *
     * @return  Varien_Data_Collection_Db
     */
    protected function _renderFilters()
    {
        if ($this->_isFiltersRendered) {
            return $this;
        }

        foreach ($this->_filters as $filter) {
            switch ($filter['type']) {
                case 'or' :
                    $condition = $this->_conn->quoteInto($filter['field'].'=?', $filter['value']);
                    $this->_sqlSelect->orWhere($condition);
                    break;
                case 'string' :
                    $this->_sqlSelect->where($filter['value']);
                    break;
                default:
                    $condition = $this->_conn->quoteInto($filter['field'].'=?', $filter['value']);
                    $this->_sqlSelect->where($condition);
            }
        }
        $this->_isFiltersRendered = true;
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * If $attribute is an array will add OR condition with following format:
     * array(
     *     array('attribute'=>'firstname', 'like'=>'test%'),
     *     array('attribute'=>'lastname', 'like'=>'test%'),
     * )
     *
     * @see self::_getConditionSql for $condition
     * @param string|array $attribute
     * @param null|string|array $condition
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addFieldToFilter($field, $condition)
    {
        $this->_sqlSelect->where($this->_getConditionSql($field, $condition));
        return $this;
    }

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered
     *
     * If $condition is array is - one of the following structures is expected:
     * - array("from"=>$fromValue, "to"=>$toValue)
     * - array("like"=>$likeValue)
     * - array("neq"=>$notEqualValue)
     * - array("in"=>array($inValues))
     * - array("nin"=>array($notInValues))
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     * @return string
     */
    protected function _getConditionSql($fieldName, $condition) {
    	if (is_array($fieldName)) {
    		foreach ($fieldName as $f) {
                $orSql = array();
                foreach ($condition as $orCondition) {
                    $orSql[] = "(".$this->_getConditionSql($f[0], $f[1]).")";
                }
                $sql = "(".join(" or ", $orSql).")";
    		}
    		return $sql;
    	}

        $sql = '';
        if (is_array($condition)) {
            if (isset($condition['from']) || isset($condition['to'])) {
                if (isset($condition['from'])) {
                    $from = empty($condition['date']) ? ( empty($condition['datetime']) ? $condition['from'] : $this->getConnection()->convertDateTime($condition['from']) ) : $this->getConnection()->convertDate($condition['from']);
                    $sql.= $this->getConnection()->quoteInto("$fieldName >= ?", $from);
                }
                if (isset($condition['to'])) {
                    $sql.= empty($sql) ? '' : ' and ';
                    $to = empty($condition['date']) ? ( empty($condition['datetime']) ? $condition['to'] : $this->getConnection()->convertDateTime($condition['to']) ) : $this->getConnection()->convertDate($condition['to']);
                    $sql.= $this->getConnection()->quoteInto("$fieldName <= ?", $to);
                }
            }
            elseif (isset($condition['neq'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName != ?", $condition['neq']);
            }
            elseif (isset($condition['like'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName like ?", $condition['like']);
            }
            elseif (isset($condition['nlike'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName not like ?", $condition['nlike']);
            }
            elseif (isset($condition['in'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName in (?)", $condition['in']);
            }
            elseif (isset($condition['nin'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName not in (?)", $condition['nin']);
            }
            elseif (isset($condition['notnull'])) {
                $sql = "$fieldName is NOT NULL";
            }
            elseif (isset($condition['null'])) {
                $sql = "$fieldName is NULL";
            }
            elseif (isset($condition['moreq'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName >= ?", $condition['moreq']);
            }
            elseif (isset($condition['gt'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName > ?", $condition['gt']);
            }
            elseif (isset($condition['lt'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName < ?", $condition['lt']);
            }
            elseif (isset($condition['gteq'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName >= ?", $condition['gteq']);
            }
            elseif (isset($condition['lteq'])) {
                $sql = $this->getConnection()->quoteInto("$fieldName <= ?", $condition['lteq']);
            }
            else {
                $orSql = array();
                foreach ($condition as $orCondition) {
                    $orSql[] = "(".$this->_getConditionSql($fieldName, $orCondition).")";
                }
                $sql = "(".join(" or ", $orSql).")";
            }
        } else {
            $sql = $this->getConnection()->quoteInto("$fieldName = ?", $condition);
        }
        return $sql;
    }

    /**
     * Render sql select orders
     *
     * @return  Varien_Data_Collection_Db
     */
    protected function _renderOrders()
    {
        foreach ($this->_orders as $orderExpr) {
            $this->_sqlSelect->order($orderExpr);
        }
        return $this;
    }

    /**
     * Render sql select limit
     *
     * @return  Varien_Data_Collection_Db
     */
    protected function _renderLimit()
    {
        if($this->_pageSize){
            $this->_sqlSelect->limitPage($this->getCurPage(), $this->_pageSize);
        }

        return $this;
    }

    /**
     * Set select distinct
     *
     * @param bool $flag
     */
    public function distinct($flag)
    {
        $this->_sqlSelect->distinct($flag);
        return $this;
    }

    /**
     * Load data
     *
     * @return  Varien_Data_Collection_Db
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_renderFilters()
             ->_renderOrders()
             ->_renderLimit();

        $this->printLogQuery($printQuery, $logQuery);
//echo $this->_sqlSelect;
        $data = $this->_conn->fetchAll($this->_sqlSelect);
        if (is_array($data)) {
            foreach ($data as $row) {
                $item = new $this->_itemObjectClass();
                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $item->addData($row);
                $this->addItem($item);
            }
        }

        $this->_setIsLoaded();
        return $this;
    }

    public function loadData($printQuery = false, $logQuery = false)
    {
        return $this->load($printQuery, $logQuery);
    }

    /**
     * Print and/or log query
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return  Varien_Data_Collection_Db
     */
    public function printLogQuery($printQuery = false, $logQuery = false, $sql = null) {
        if ($printQuery) {
            echo is_null($sql) ? $this->_sqlSelect->__toString() : $sql;
        }

        if ($logQuery){
            Mage::log(is_null($sql) ? $this->_sqlSelect->__toString() : $sql);
        }
        return $this;
    }


}