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
 * @package    Mage_Page
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 *
 * @todo        separate order, mode and pager
 */
class Mage_Page_Block_Html_Pager extends Mage_Core_Block_Template
{
    protected $_collection = null;
    protected $_pageVarName     = 'p';
    protected $_limitVarName    = 'limit';
    protected $_availableLimit  = array(10=>10,20=>20,50=>50);
    protected $_dispersion      = 3;
    protected $_showPerPage		= true;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('page/html/pager.phtml');
    }

    public function getCurrentPage()
    {
        if ($page = (int) $this->getRequest()->getParam($this->getPageVarName())) {
            return $page;
        }
        return 1;
    }

    public function getLimit()
    {
        $limits = $this->getAvailableLimit();
        if ($limit = $this->getRequest()->getParam($this->getLimitVarName())) {
            if (isset($limits[$limit])) {
                return $limit;
            }
        }
        $limits = array_keys($limits);
        return $limits[0];
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection
            ->setCurPage($this->getCurrentPage());
        // If not int - then not limit
        if ((int) $this->getLimit()) {
            $this->_collection->setPageSize($this->getLimit());
        }

        return $this;
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    public function setPageVarName($varName)
    {
        $this->_pageVarName = $varName;
        return $this;
    }

    public function getPageVarName()
    {
        return $this->_pageVarName;
    }
    
    public function setShowPerPage($varName)
    {
    	$this->_showPerPage=$varName;
    }
    
    public function getShowPerPage()
    {
        if(sizeof($this->getAvailableLimit())<=1) {
            return false; 
        }
    	return $this->_showPerPage;
    }

    public function setLimitVarName($varName)
    {
        $this->_limitVarName = $varName;
        return $this;
    }

    public function getLimitVarName()
    {
        return $this->_limitVarName;
    }

    public function setAvailableLimit(array $limits)
    {
        $this->_availableLimit = $limits;
    }

    public function getAvailableLimit()
    {
        return $this->_availableLimit;
    }

    public function getFirstNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize()*($collection->getCurPage()-1)+1;
    }

    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize()*($collection->getCurPage()-1)+$collection->count();
    }

    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    public function isLastPage()
    {
        return $this->getCollection()->getCurPage() >= $this->getLastPageNum();
    }

    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    public function isPageCurrent($page)
    {
        return $page == $this->getCurrentPage();
    }

    public function getPages()
    {
        $pages = array();
        for ($i=$this->getCollection()->getCurPage(-$this->_dispersion); $i <= $this->getCollection()->getCurPage(+($this->_dispersion-1)); $i++)
        {
            $pages[] = $i;
        }

        return $pages;
    }

    public function getFirstPageUrl()
    {
        return $this->getPageUrl(1);
    }

    public function getPreviousPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage(-1));
    }

    public function getNextPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage(+1));
    }

    public function getLastPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getLastPageNumber());
    }

    public function getPageUrl($page)
    {
        return $this->getPagerUrl(array($this->getPageVarName()=>$page));
    }

    public function getLimitUrl($limit)
    {
        return $this->getPagerUrl(array($this->getLimitVarName()=>$limit));
    }

    public function getPagerUrl($params=array())
    {
        $params['_current'] = true;
        return $this->getUrl('*/*/*', $params);
    }
}

