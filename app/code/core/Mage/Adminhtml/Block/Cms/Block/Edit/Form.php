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
 * Adminhtml cms block edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Cms_Block_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('block_form');
        $this->setTitle(Mage::helper('cms')->__('Block Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('cms_block');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $form->setHtmlIdPrefix('block_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('cms')->__('General Information')));

        if ($model->getBlockId()) {
        	$fieldset->addField('block_id', 'hidden', array(
                'name' => 'block_id',
            ));
        }

    	$fieldset->addField('title', 'text', array(
            'name' => 'title',
            'label' => Mage::helper('cms')->__('Block Title'),
            'title' => Mage::helper('cms')->__('Block Title'),
            'required' => true,
        ));

    	$fieldset->addField('identifier', 'text', array(
            'name' => 'identifier',
            'label' => Mage::helper('cms')->__('Identifier'),
            'title' => Mage::helper('cms')->__('Identifier'),
            'required' => true,
        ));

        $stores = Mage::getResourceModel('core/store_collection')->load()->toOptionHash();
        $stores[0] = Mage::helper('cms')->__('All Store Views');

    	$fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => Mage::helper('cms')->__('Store'),
            'title'     => Mage::helper('cms')->__('Store'),
            'required'  => true,
            'options'    => $stores,
        ));

    	$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('cms')->__('Status'),
            'title'     => Mage::helper('cms')->__('Status'),
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

        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
