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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin page left menu
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Cms_Page_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('cms')->__('Page Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('cms')->__('General Information'),
            'title'     => Mage::helper('cms')->__('General Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/cms_page_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('meta_section', array(
            'label'     => Mage::helper('cms')->__('Meta Data'),
            'title'     => Mage::helper('cms')->__('Meta Data'),
            'content'   => $this->getLayout()->createBlock('adminhtml/cms_page_edit_tab_meta')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
