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
 * @package    Mage_Log
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Mage_Log_Model_Mysql4_Customers_Collection
 *
 * @category   Mage
 * @package    Mage_Log
 */

class Mage_Log_Model_Mysql4_Visitor_Collection extends Varien_Data_Collection_Db
{
    /**
     * Visitor data table name
     *
     * @var string
     */
    protected $_visitorTable;

    /**
     * Visitor data info table name
     *
     * @var string
     */
    protected $_visitorInfoTable;

    /**
     * Customer data table
     *
     * @var string
     */
    protected $_customerTable;

    /**
     * Log URL data table name.
     *
     * @var string
     */
    protected $_urlTable;

    /**
     * Log URL expanded data table name.
     *
     * @var string
     */
    protected $_urlInfoTable;

    /**
     * Aggregator data table.
     *
     * @var string
     */
    protected $_summaryTable;

    /**
     * Aggregator type data table.
     *
     * @var string
     */
    protected $_summaryTypeTable;

    /**
     * Quote data table.
     *
     * @var string
     */
    protected $_quoteTable;

    /**
     * Construct
     *
     */
    function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        parent::__construct($resource->getConnection('log_read'));

        $this->_visitorTable = $resource->getTableName('log/visitor');
        $this->_visitorInfoTable = $resource->getTableName('log/visitor_info');
        $this->_urlTable = $resource->getTableName('log/url_table');
        $this->_urlInfoTable = $resource->getTableName('log/url_info_table');
        $this->_customerTable = $resource->getTableName('log/customer');
        $this->_summaryTable = $resource->getTableName('log/summary_table');
        $this->_summaryTypeTable = $resource->getTableName('log/summary_type_table');
        $this->_quoteTable = $resource->getTableName('log/quote_table');

        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('log/visitor'));
    }

    /**
     * Enables online only select
     *
     * @param int $minutes
     * @return object
     */
    public function useOnlineFilter($minutes=null)
    {
        if (is_null($minutes)) {
            $minutes = Mage_Log_Model_Visitor::ONLINE_MINUTES_INTERVAL;
        }
        $this->_sqlSelect->from(array('visitor_table'=>$this->_visitorTable))
            //->joinLeft(array('url_table'=>$this->_urlTable), 'visitor_table.last_url_id=url_table.url_id')
            ->joinLeft(array('info_table'=>$this->_visitorInfoTable), 'info_table.visitor_id=visitor_table.visitor_id')
            ->joinLeft(array('customer_table'=>$this->_customerTable),
                'customer_table.visitor_id = visitor_table.visitor_id AND customer_table.logout_at IS NULL',
                array('log_id', 'customer_id', 'login_at', 'logout_at'))
            ->joinLeft(array('url_info_table'=>$this->_urlInfoTable),
                'url_info_table.url_id = visitor_table.last_url_id')
            //->joinLeft(array('quote_table'=>$this->_quoteTable), 'quote_table.visitor_id=visitor_table.visitor_id')
            ->where( 'visitor_table.last_visit_at >= ( ? - INTERVAL '.$minutes.' MINUTE)', now() );
        return $this;
    }

    public function showCustomersOnly()
    {
        $this->_sqlSelect->where('customer_table.customer_id > 0')
            ->group('customer_table.customer_id');
        return $this;
    }

    public function getAggregatedData($period=720, $type_code=null, $customFrom=null, $customTo=null)
    {
        /**
         * @todo : need remove agregation logic
         */
        $timeZoneOffset = Mage::app()->getLocale()->date()->getGmtOffset();
        $this->_itemObjectClass = 'Varien_Object';
        $this->_setIdFieldName('summary_id');

/*
    	$this->_sqlSelect->from(array('summary'=>$this->_summaryTable), array('summary_id','customer_count','visitor_count','add_date'=>"DATE_SUB(summary.add_date, INTERVAL $timeZoneOffset SECOND)"))
    	   ->join(array('type'=>$this->_summaryTypeTable), 'type.type_id=summary.type_id', array());

    	if (is_null($customFrom) && is_null($customTo)) {
    	   $this->_sqlSelect->where( "DATE_SUB(summary.add_date, INTERVAL $timeZoneOffset SECOND) >= ( DATE_SUB(?, INTERVAL $timeZoneOffset SECOND) - INTERVAL {$period} {$this->_getRangeByType($type_code)} )", now() );
    	} else {
    	    if($customFrom) {
     	        $this->_sqlSelect->where( "DATE_SUB(summary.add_date, INTERVAL $timeZoneOffset SECOND) >= ", $this->_read->convertDate($customFrom));
     	    }
     	    if($customTo) {
     	        $this->_sqlSelect->where( "DATE_SUB(summary.add_date, INTERVAL $timeZoneOffset SECOND) <= ", $this->_read->convertDate($customTo));
     	    }
    	}


    	if( is_null($type_code) ) {
    		$this->_sqlSelect->where("summary.type_id IS NULL");
    	} else {
		    $this->_sqlSelect->where("type.type_code = ? ", $type_code);
    	}
*/

    	$this->_sqlSelect->from(array('summary'=>$this->_summaryTable),
            array('summary_id',
                'customer_count'=>'SUM(customer_count)',
                'visitor_count'=>'SUM(visitor_count)',
                'add_date'=>"DATE_SUB(summary.add_date, INTERVAL $timeZoneOffset SECOND)"
        ));

        $this->_sqlSelect->where("DATE_SUB(summary.add_date, INTERVAL $timeZoneOffset SECOND) >= ( DATE_SUB(?, INTERVAL $timeZoneOffset SECOND) - INTERVAL {$period} {$this->_getRangeByType($type_code)} )", now() );
    	$this->_sqlSelect->group('DATE_FORMAT(add_date, \''.$this->_getGroupByDateFormat($type_code).'\')');
    	$this->_sqlSelect->order('add_date ASC');

    	return $this;
    }

    protected function _getGroupByDateFormat($type)
    {
        switch ($type) {
            case 'day':
                $format = '%Y-%m-%d';
                break;
            default:
            case 'hour':
                $format = '%Y-%m-%d %H';
                break;
        }
        return $format;
    }

    protected function _getRangeByType($type_code)
    {
        switch ($type_code)
        {
            case 'day':
                $range = 'DAY';
                break;
            case 'hour':
                $range = 'HOUR';
                break;
            case 'minute':
            default:
                $range = 'MINUTE';
                break;

        }

        return $range;
    }

    public function addFieldToFilter($fieldName, $fieldValue)
    {
        if( $fieldName == 'type' ) {
            if ($fieldValue == 'v') {
                return parent::addFieldToFilter('customer_id', array('null' => 1));
            } else {
                return parent::addFieldToFilter('customer_id', array('moreq' => 1));
            }
        } else {
            return parent::addFieldToFilter($fieldName, $fieldValue);
        }
    }
}