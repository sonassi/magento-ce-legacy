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
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tagged products controller
 *
 * @category   Mage
 * @package    Mage_Tag
 */

class Mage_Tag_ProductController extends Mage_Core_Controller_Front_Action
{
    public function listAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('tag/session');
        $tagId = $this->getRequest()->getParam('tagId');

        if( intval($tagId) <= 0 ) {
            $this->_redirectReferer();
            return;
        }

        Mage::register('tagId', $tagId);
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('tag/product_result'));

        $this->renderLayout();
    }
}