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
 * Cms page edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('cms_page');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('cms')->__('General Information')));

        if ($model->getPageId()) {
        	$fieldset->addField('page_id', 'hidden', array(
                'name' => 'page_id',
            ));
        }

    	$fieldset->addField('title', 'text', array(
            'name' => 'title',
            'label' => Mage::helper('cms')->__('Page Title'),
            'title' => Mage::helper('cms')->__('Page Title'),
            'required' => true,
        ));

    	$fieldset->addField('identifier', 'text', array(
            'name' => 'identifier',
            'label' => Mage::helper('cms')->__('SEF URL Identifier'),
            'title' => Mage::helper('cms')->__('SEF URL Identifier'),
            'required' => true,
            'after_element_html' => '<span class="hint">' . Mage::helper('cms')->__('(eg: domain.com/identifier)') . '</span>',
        ));

        $stores = Mage::getResourceModel('core/store_collection')->load()->toOptionHash();
        $stores[0] = Mage::helper('cms')->__('All stores');

    	$fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => Mage::helper('cms')->__('Store'),
            'title'     => Mage::helper('cms')->__('Store'),
            'required'  => true,
            'options'    => $stores,
        ));

        $layouts = array();
        foreach (Mage::getConfig()->getNode('global/cms/layouts')->children() as $layoutName=>$layoutConfig) {
        	$layouts[$layoutName] = (string)$layoutConfig->label;
        }
    	$fieldset->addField('root_template', 'select', array(
            'name'      => 'root_template',
            'label'     => Mage::helper('cms')->__('Layout'),
            'required'  => true,
            'options'    => $layouts,
        ));

    	$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('cms')->__('Status'),
            'title'     => Mage::helper('cms')->__('Page Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('cms')->__('Enabled'),
                '0' => Mage::helper('cms')->__('Disabled'),
            ),
        ));

    	$fieldset->addField('content', 'editor', array(
            'name' => 'content',
            'label' => Mage::helper('cms')->__('Content'),
            'title' => Mage::helper('cms')->__('Content'),
            'style' => 'width: 98%; height: 600px;',
            'wysiwyg' => false,
            'required' => true,
        ));

        $fieldset->addField('layout_update_xml', 'editor', array(
            'name' => 'layout_update_xml',
            'label' => Mage::helper('cms')->__('Layout Update XML'),
            'style' => 'width:98%'
        ));

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
