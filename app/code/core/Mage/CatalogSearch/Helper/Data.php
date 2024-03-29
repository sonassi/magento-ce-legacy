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
 * @package    Mage_CatalogSearch
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog search helper
 *
 */
class Mage_CatalogSearch_Helper_Data extends Mage_Core_Helper_Abstract
{
    const QUERY_VAR_NAME = 'q';
    const MAX_QUERY_LEN  = 200;

    protected $_query;
    protected $_queryText;

    /**
     * Retrieve search query parameter name
     *
     * @return string
     */
    public function getQueryParamName()
    {
        return self::QUERY_VAR_NAME;
    }

    /**
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        if (!$this->_query) {
            $this->_query = Mage::getModel('catalogsearch/query')
                ->load($this->getQueryText())
                ->setQueryText($this->getQueryText());
        }
        return $this->_query;
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    public function getQueryText()
    {
        if (is_null($this->_queryText)) {
            $this->_queryText = $this->_getRequest()->getParam($this->getQueryParamName());
            $this->_queryText = iconv_substr(trim(strip_tags($this->_queryText)), 0, self::MAX_QUERY_LEN);

            /**
             * Fix problem with
             * SQLSTATE[HY093]: Invalid parameter number: no parameters were bound
             */
            $this->_queryText = str_replace('?', '', $this->_queryText);
        }
        return $this->_queryText;
    }

    /**
     * Retrieve escaped search query
     *
     * @return string
     */
    public function getEscapedQueryText()
    {
        return $this->htmlEscape($this->getQueryText());
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return unknown
     */
    public function getSuggestCollection()
    {
        return $this->getQuery()->getSuggestCollection();
    }

    /**
     * Retrieve result page url
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrl($query='')
    {
        $params = array();
        return $this->_getUrl('catalogsearch/result');
    }

    /**
     * Retrieve suggest url
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        return $this->_getUrl('catalogsearch/ajax/suggest');
    }

    /**
     * Retrieve search term url
     *
     * @return string
     */
    public function getSearchTermUrl()
    {
        return $this->_getUrl('catalogsearch/term/popular');
    }


    public function getAdvancedSearchUrl()
    {
        return $this->_getUrl('catalogsearch/advanced');
    }
}
