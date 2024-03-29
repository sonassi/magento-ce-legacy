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
 * Catalog Search Controller
 */
class Mage_CatalogSearch_ResultController extends Mage_Core_Controller_Front_Action
{
    /**
     * Display search result
     */
    public function indexAction()
    {
        $query = Mage::helper('catalogSearch')->getQuery();

        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($text = $query->getQueryText()) {
            if ($query->getId()) {
                $query->setPopularity($query->getPopularity()+1);
            }
            else {
                $query->setPopularity(1);
            }

            if ($query->getRedirect()){
                $query->save();
                $this->getResponse()->setRedirect($query->getRedirect());
                return;
            }

            $this->loadLayout();
            $this->renderLayout();
            $query->save();
        }
        else {
            $this->_redirectReferer();
        }
    }
}
