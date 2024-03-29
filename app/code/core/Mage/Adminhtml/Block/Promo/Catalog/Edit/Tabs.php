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
 * description
 *
 * @category    Mage
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Promo_Catalog_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('promo_catalog_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('catalogrule')->__('Catalog Price Rule'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('catalogrule')->__('Rule Information'),
            'title'     => Mage::helper('catalogrule')->__('Rule Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/promo_catalog_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('condact_section', array(
            'label'     => Mage::helper('catalogrule')->__('Conditions and Actions'),
            'title'     => Mage::helper('catalogrule')->__('Conditions and Actions'),
            'content'   => $this->getLayout()->createBlock('adminhtml/promo_catalog_edit_tab_condact')->toHtml(),
        ));
        /*
        $this->addTab('actions_section', array(
            'label'     => Mage::helper('catalogrule')->__('Actions'),
            'title'     => Mage::helper('catalogrule')->__('Actions'),
            'content'   => $this->getLayout()->createBlock('adminhtml/promo_catalog_edit_tab_actions')->toHtml(),
        ));
        */
        return parent::_beforeToHtml();
    }

}
