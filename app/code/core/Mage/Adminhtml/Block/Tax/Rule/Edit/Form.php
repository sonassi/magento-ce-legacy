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
 * Adminhtml Tax Rule Edit Form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Rule_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('taxRuleForm');
        $this->setTitle(Mage::helper('tax')->__('Tax Rule Information'));
    }

    /**
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model  = Mage::registry('tax_rule');
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('tax')->__('Tax Rule Information')
        ));

        $rates  = Mage::getModel('tax/rate_type')
            ->getCollection()
            ->toOptionArray();

        $productClasses = Mage::getModel('tax/class')
            ->getCollection()
            ->setClassTypeFilter('PRODUCT')
            ->toOptionArray();

        $customerClasses = Mage::getModel('tax/class')
            ->getCollection()
            ->setClassTypeFilter('CUSTOMER')
            ->toOptionArray();

        $fieldset->addField('tax_customer_class_id', 'select',
            array(
                'name'      => 'tax_customer_class_id',
                'label'     => Mage::helper('tax')->__('Customer Tax Class'),
                'class'     => 'required-entry',
                'values'    => $customerClasses,
                'value'     => $model->getTaxCustomerClassId(),
                'required'  => true,
            )
        );

        $fieldset->addField('tax_product_class_id', 'select',
            array(
                'name'      => 'tax_product_class_id',
                'label'     => Mage::helper('tax')->__('Product Tax Class'),
                'class'     => 'required-entry',
                'values'    => $productClasses,
                'value'     => $model->getTaxProductClassId(),
                'required'  => true,
            )
        );

        $fieldset->addField('tax_rate_type_id', 'select',
            array(
                'name'      => 'tax_rate_type_id',
                'label'     => Mage::helper('tax')->__('Customer Tax Class'),
                'class'     => 'required-entry',
                'values'    => $rates,
                'value'     => $model->getTaxRateTypeId(),
                'required'  => true,
            )
        );

        if ($model->getId() > 0 ) {
            $fieldset->addField('tax_rule_id', 'hidden',
                array(
                    'name'      => 'tax_rule_id',
                    'value'     => $model->getId(),
                    'no_span'   => true
                )
            );
        }

        $form->setAction(Mage::getUrl('*/tax_rule/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}