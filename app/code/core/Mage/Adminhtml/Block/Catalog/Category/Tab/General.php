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
 * Category edit general tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Category_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_category;

    public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::registry('category');
        }
        return $this->_category;
    }

    public function _prepareLayout()
    {
	parent::_prepareLayout();
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_general');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('catalog')->__('General Information')));

        $this->_setFieldset($this->getCategory()->getAttributes(true), $fieldset);

        if (!$this->getCategory()->getId()) {
            $fieldset->addField('parent_id', 'select', array(
                'name'  => 'parent_id',
                'label' => Mage::helper('catalog')->__('Parent Category'),
                'value' => $this->getRequest()->getParam('parent'),
                'values'=> $this->_getParentCategoryOptions(),
                //'required' => true,
                //'class' => 'required-entry'
                ),
                'name'
            );
        }

        $form->addValues($this->getCategory()->getData());

        $form->setFieldNameSuffix('general');
        $this->setForm($form);
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_category_helper_image')
        );
    }

    protected function _getParentCategoryOptions($node=null, &$options=array())
    {
        if (is_null($node)) {
            $node = $this->getLayout()->getBlock('category.tree')->getRoot();
        }

        if ($node) {
            $options[] = array(
               'value' => $node->getId(),
               'label' => $node->getName(),
               'style' => 'padding-left:'.(10*$node->getLevel()).'px',
            );

            foreach ($node->getChildren() as $child) {
                $this->_getParentCategoryOptions($child, $options);
            }
        }
        return $options;
    }
}
