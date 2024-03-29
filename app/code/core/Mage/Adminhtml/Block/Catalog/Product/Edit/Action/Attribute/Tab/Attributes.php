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
 * Adminhtml catalog product edit action attributes update tab block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Attributes extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('fields', array('legend'=>Mage::helper('catalog')->__('Attributes')));
        $attributes = $this->getAttributes();

        $this->_setFieldset($attributes, $fieldset, array('tier_price','gallery'));
        $form->setFieldNameSuffix('attributes');
        $this->setForm($form);
    }

    /**
     * Retrive attributes for product massupdate
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->helper('adminhtml/catalog_product_edit_action_attribute')->getAttributes()->getItems();
    }

    /**
     * Additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'price' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_price'),
            'image' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_image'),
            'boolean' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean')
        );
    }

    /**
     * Custom additional elemnt html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '<span class="attribute-change-checkbox"><input type="checkbox" id="' . $element->getId()
             . '-checkbox" onclick="toogleFieldEditMode(this, \'' . $element->getId()
             . '\')" /><label for="' . $element->getId() . '-checkbox">' . Mage::helper('catalog')->__('Change')
             . '</label></span>
                <script type="text/javascript">initDisableFields(\''.$element->getId().'\')</script>';
    }
} // Class Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Attributes End