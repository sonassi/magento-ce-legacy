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
 * Adminhtml Review Edit Form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Review_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $review = Mage::registry('review_data');
        $product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
        $customer = Mage::getModel('customer/customer')->load($review->getCustomerId());
        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

         $stores = Mage::app()->getStore()->getResourceCollection()->load()->toOptionArray();

        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => Mage::getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'), 'ret' => Mage::registry('ret'))),
                                        'method' => 'post',
                                     )
        );

        $fieldset = $form->addFieldset('review_details', array('legend' => Mage::helper('review')->__('Review Details')));

        $fieldset->addField('product_name', 'note', array(
                                'label'     => Mage::helper('review')->__('Product'),
                                'text'      => '<a href="' . Mage::getUrl('*/catalog_product/edit', array('id' => $product->getId())) . '" target="_blank">' . $product->getName() . '</a>',
                            )
        );

        if($customer->getId()) {
            $customerText = Mage::helper('review')->__('<a href="%1$s" target="_blank">%2$s %3$s</a> <a href="mailto:%4$s">(%4$s)</a>', Mage::getUrl('*/customer/edit', array('id' => $customer->getId(), 'active_tab'=>'review')), $customer->getFirstname(), $customer->getLastname(), $customer->getEmail());
        } else {
            $customerText  = Mage::helper('review')->__('Guest');
        }

        $fieldset->addField('customer', 'note', array(
                                'label'     => Mage::helper('review')->__('Posted By'),
                                'text'      => $customerText,
                            )
        );

        $fieldset->addField('summary_rating', 'note', array(
                                'label'     => Mage::helper('review')->__('Summary Rating'),
                                'text'      => $this->getLayout()->createBlock('adminhtml/review_rating_summary')->toHtml(),
                            )
        );

        $fieldset->addField('detailed_rating', 'note', array(
                                'label'     => Mage::helper('review')->__('Detailed Rating'),
                                'required'  => true,
                                'text'      => '<div id="rating_detail">' . $this->getLayout()->createBlock('adminhtml/review_rating_detailed')->toHtml() . '</div>',
                            )
        );

        $fieldset->addField('status_id', 'select', array(
                                'label'     => Mage::helper('review')->__('Status'),
                                'required'  => true,
                                'name'      => 'status_id',
                                'values'    => $statuses,
                            )
        );

        $fieldset->addField('select_stores', 'multiselect', array(
                                'label'     => Mage::helper('review')->__('Visible In'),
                                'required'  => true,
                                'name'      => 'stores[]',
                                'values'    => $stores
                            )
        );





        $fieldset->addField('nickname', 'text', array(
                                'label'     => Mage::helper('review')->__('Nickname'),
                                'required'  => true,
                                'name'      => 'nickname',
                            )
        );

        $fieldset->addField('title', 'text', array(
                                'label'     => Mage::helper('review')->__('Summary of review'),
                                'required'  => true,
                                'name'      => 'title',
                            )
        );

        $fieldset->addField('detail', 'textarea', array(
                                'label'     => Mage::helper('review')->__('Review'),
                                'required'  => true,
                                'name'      => 'detail',
                                'style' => 'width: 98%; height: 600px;',
                            )
        );

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $form->getElement('select_stores')->setValue($review->getStores());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}