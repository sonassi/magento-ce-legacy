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
 * @package    Mage_Review
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review Product Collection
 *
 * @category   Mage
 * @package    Mage_Review
 */

class Mage_Review_Model_Mysql4_Review_Product_Collection extends Mage_Catalog_Model_Entity_Product_Collection
{
    protected $_entitiesAlias = array();
    protected $_reviewStoreTable;
    protected $_addStoreDataFlag = false;

    public function __construct()
    {
        $this->setEntity(Mage::getResourceSingleton('catalog/product'));
        $this->setObject('catalog/product');
        $this->setRowIdFieldName('review_id');
        $this->_reviewStoreTable = Mage::getSingleton('core/resource')->getTableName('review/review_store');
    }

    public function addStoreFilter($storeId)
    {
        $pstoreTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_store');
        $this->getSelect()
            ->join(array('store'=>$this->_reviewStoreTable),
                'rt.review_id=store.review_id AND store.store_id=' . (int)$storeId, array())
            ->join(array('pstore'=>$pstoreTable),
                'pstore.product_id=e.entity_id AND pstore.store_id=' . (int)$storeId, array());

        return $this;
    }

    public function setStoreFilter($storeId)
    {
        $this->getSelect()
            ->join(array('store'=>$this->_reviewStoreTable),
                'rt.review_id=store.review_id AND store.store_id=' . (int)$storeId, array());
        return $this;
    }


    /**
     * Add stores data
     *
     * @param   int $storeId
     * @return  Varien_Data_Collection_Db
     */
    public function addStoreData()
    {
        $this->_addStoreDataFlag = true;
        return $this;
    }
    public function addCustomerFilter($customerId)
    {
        $this->getSelect()
            ->where('rdt.customer_id = ?', $customerId);
        return $this;
    }

    public function addEntityFilter($entityId)
    {
        $this->getSelect()
            ->where('rt.entity_pk_value = ?', $entityId);
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()
            ->where('rt.status_id = ?', $status);
        return $this;
    }

    public function setDateOrder($dir='DESC')
    {
        $this->setOrder('rt.created_at', $dir);
        return $this;
    }

    public function addReviewSummary()
    {
        foreach( $this->getItems() as $item ) {
            $model = Mage::getModel('rating/rating');
            $model->getReviewSummary($item->getReviewId());
            $item->addData($model->getData());
        }
        return $this;
    }

    public function addRateVotes()
    {
        foreach( $this->getItems() as $item ) {
            $votesCollection = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection()
                ->setEntityPkFilter($item->getEntityId())
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->load();
            $item->setRatingVotes( $votesCollection );
        }
        return $this;
    }

    public function resetSelect()
    {
        parent::resetSelect();
        $this->_joinFields();
        return $this;
    }

    protected function _joinFields()
    {
        $reviewTable = Mage::getSingleton('core/resource')->getTableName('review/review');
        $reviewDetailTable = Mage::getSingleton('core/resource')->getTableName('review/review_detail');

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('sku');

        $this->getSelect()
            ->join(array('rt' => $reviewTable),
                'rt.entity_pk_value = e.entity_id',
                array('review_id', 'created_at', 'entity_pk_value', 'status_id'))
            ->join(array('rdt' => $reviewDetailTable), 'rdt.review_id = rt.review_id');
    }

    /**
     * Render SQL for retrieve product count
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        $sql = $countSelect->__toString();
        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(e.entity_id) from ', $sql);

        return $sql;
    }

    public function setOrder($attribute, $dir='desc')
    {
        switch( $attribute ) {
            case 'rt.review_id':
            case 'rt.created_at':
            case 'rt.status_id':
            case 'rdt.title':
            case 'rdt.nickname':
            case 'rdt.detail':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            case 'stores':
                // No way to sort
                break;
            case 'type':
                $this->getSelect()->order('rdt.customer_id ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    public function addAttributeToFilter($attribute, $condition=null)
    {
        switch( $attribute ) {
            case 'rt.review_id':
            case 'rt.created_at':
            case 'rt.status_id':
            case 'rdt.title':
            case 'rdt.nickname':
            case 'rdt.detail':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                return $this;
                break;
           case 'stores':
                $this->setStoreFilter($condition);
                return $this;
                break;
            case 'type':
                if($condition) {
                    $this->getSelect()->where('rdt.customer_id > 0');
                } else {
                    $this->getSelect()->where('(rdt.customer_id IS NULL) OR (rdt.customer_id = 0)');
                }
                return $this;
                break;

            default:
                parent::addAttributeToFilter($attribute, $condition);
        }
        return $this;
    }

    public function getColumnValues($colName)
    {
        $col = array();
        foreach ($this->getItems() as $item) {
            $col[] = $item->getData($colName);
        }
        return $col;
    }


    public function load($printQuery=false, $logQuery=false)
    {
        parent::load($printQuery, $logQuery);
        if($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
        return $this;
    }

    protected function _addStoreData()
    {
        $reviewsIds = $this->getColumnValues('review_id');
        $storesToReviews = array();
        if (count($reviewsIds)>0) {
            $select = $this->_read->select()
                ->from($this->_reviewStoreTable)
                ->where('review_id IN(?)', $reviewsIds);
            $result = $this->_read->fetchAll($select);
            foreach ($result as $row) {
                if (!isset($storesToReviews[$row['review_id']])) {
                    $storesToReviews[$row['review_id']] = array();
                }
                $storesToReviews[$row['review_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if(isset($storesToReviews[$item->getReviewId()])) {
                $item->setData('stores',$storesToReviews[$item->getReviewId()]);
            } else {
                $item->setData('stores', array());
            }

        }
    }
 }