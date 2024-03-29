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
 * Advanced search result
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 */
class Mage_CatalogSearch_Block_Advanced_Result extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('breadcrumbs')
            ->addCrumb('home',
                array('label'=>Mage::helper('catalogsearch')->__('Home'),
                    'title'=>Mage::helper('catalogsearch')->__('Go to Home Page'),
                    'link'=>Mage::getBaseUrl())
            )
            ->addCrumb('search',
                array('label'=>Mage::helper('catalogsearch')->__('Catalog Advanced Search'), 'link'=>$this->getUrl('*/*/'))
            )
            ->addCrumb('search_result',
                array('label'=>Mage::helper('catalogsearch')->__('Results'))
            );

        $resultBlock = $this->getLayout()
            ->createBlock('catalog/product_list', 'product_list')
            ->setAvailableOrders(array('name'=>Mage::helper('catalogsearch')->__('Name'), 'price'=>Mage::helper('catalogsearch')->__('Price')))
            ->setModes(array('grid'=>Mage::helper('catalogsearch')->__('Grid'), 'list' => Mage::helper('catalogsearch')->__('List')))
            ->setCollection($this->_getProductCollection());

        $this->setChild('search_result_list', $resultBlock);
        return parent::_prepareLayout();
    }

    protected function _getProductCollection(){
        return $this->getSearchModel()->getProductCollection();
    }

    public function getSearchModel()
    {
        return Mage::getSingleton('catalogsearch/advanced');
    }

    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->getSearchModel()->getProductCollection()->getSize();
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }

    public function getFormUrl()
    {
        return Mage::getModel('core/url')
            ->setQueryParams($this->getRequest()->getQuery())
            ->getUrl('*/*/');
    }

    public function getSearchCriterias()
    {
        $searchCriterias = $this->getSearchModel()->getSearchCriterias();
        $middle = ceil(count($searchCriterias) / 2);
        $left = array_slice($searchCriterias, 0, $middle);
        $right = array_slice($searchCriterias, $middle);

        return array('left'=>$left, 'right'=>$right);
    }
}
