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
class Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Condact extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('promo/form.phtml');
        $this->setNewConditionChildUrl($this->getUrl('*/promo_quote/newConditionHtml'));
        $this->setNewActionChildUrl($this->getUrl('*/promo_quote/newActionHtml'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('current_promo_quote_rule');

        //$form = new Varien_Data_Form(array('id' => 'edit_form1', 'action' => $this->getData('action'), 'method' => 'post'));
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('conditions_fieldset', array('legend'=>Mage::helper('salesrule')->__('Conditions')));
		/*
    	$fieldset->addField('use_conditions', 'checkbox', array(
            'name' => 'use_conditions',
            'label' => Mage::helper('salesrule')->__('Use advanced conditions'),
        ));
        */
    	$fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('salesrule')->__('Conditions'),
            'title' => Mage::helper('salesrule')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getHelper('rule/conditions'));
        /*
        $fieldset = $form->addFieldset('actions_fieldset', array('legend'=>Mage::helper('salesrule')->__('Actions')));

    	$fieldset->addField('actions', 'text', array(
            'name' => 'actions',
            'label' => Mage::helper('salesrule')->__('Actions'),
            'title' => Mage::helper('salesrule')->__('Actions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getHelper('rule/actions'));
        */

        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}