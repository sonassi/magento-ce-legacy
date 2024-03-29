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
 * Adminhtml urlrewrite edit form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Urlrewrite_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('urlrewrite_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Block Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('urlrewrite_urlrewrite');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        //print_r($model);

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('General Information')));

        if ($model->getId()) {
        	$fieldset->addField('id', 'hidden', array(
                'name' => 'id',
                'value'=>$model->getId()
            ));
        }

		$stores = Mage::getResourceModel('core/store_collection')->setWithoutDefaultFilter()->load()->toOptionHash();

		if ($model->getId()) {
	    	$fieldset->addField('type', 'select', array(
	            'label' 	=> $this->__('Type'),
	            'title' 	=> $this->__('Type'),
	            'name' 		=> 'type',
	            'required' 	=> true,
	            'options'	=> array(
	                1 => $this->__('Category'),
	                2 => $this->__('Product'),
	                3 => $this->__('Custom')
	            ),
	            'disabled'	=> $model->getId() ? true: false,
	        ));

	    	$fieldset->addField('store_id', 'select', array(
	            'label' 		=> $this->__('Store'),
	            'title' 		=> $this->__('Store'),
	            'name' 			=> 'store_id',
	            'required' 		=> true,
	            'options'		=> $stores,
	            'disabled' 		=> true,
	            'value' 		=> $model->getStoreId()
	        ));

	    	$fieldset->addField('id_path', 'text', array(
	            'label' 		=> $this->__('ID Path'),
	            'title' 		=> $this->__('ID Path'),
	            'name' 			=> 'id_path',
	            'required' 		=> true,
	            'disabled'		=> true,
	            'value' 		=> $model->getIdPath()
	        ));

		} else {
	    	$fieldset->addField('type', 'select', array(
	            'label' 	=> $this->__('Type'),
	            'title' 	=> $this->__('Type'),
	            'name' 		=> 'type',
	            'required' 	=> true,
	            'options'	=> array(
	                1 => $this->__('Category'),
	                2 => $this->__('Product'),
	                3 => $this->__('Custom')
	            )
	        ));

	    	$fieldset->addField('store_id', 'select', array(
	            'label' 		=> $this->__('Store'),
	            'title' 		=> $this->__('Store'),
	            'name' 			=> 'store_id',
	            'required' 		=> true,
	            'options'		=> $stores,
	            'value' 		=> $model->getStoreId()
	        ));

	    	$fieldset->addField('id_path', 'text', array(
	            'label' 		=> $this->__('ID Path'),
	            'title' 		=> $this->__('ID Path'),
	            'name' 			=> 'id_path',
	            'required' 		=> true,
	            'value' 		=> $model->getIdPath()
	        ));
		}


		$fieldset->addField('target_path', 'text', array(
            'label'			=> $this->__('Target Path'),
            'title'			=> $this->__('Target Path'),
            'name'			=> 'target_path',
            'required'		=> true,
            'disabled'		=> true,
            'value'			=> $model->getTargetPath()
        ));

    	$fieldset->addField('request_path', 'text', array(
            'label' 		=> $this->__('Request Path'),
            'title' 		=> $this->__('Request Path'),
            'name' 	=> 'request_path',
            'required' 		=> true,
            'value' 		=> $model->getRequestPath()
        ));

    	$fieldset->addField('options', 'select', array(
            'label' 	=> $this->__('Redirect'),
            'title' 	=> $this->__('Redirect'),
            'name' 		=> 'options',
            'options'	=> array(
            	''  => $this->__('No'),
                'R' => $this->__('Yes'),
            ),
            'value' => $model->getOptions()
        ));

    	$fieldset->addField('description', 'textarea', array(
            'label' 		=> $this->__('Description'),
            'title' 		=> $this->__('Description'),
            'name' 			=> 'description',
            'cols'			=> 20,
            'rows'			=> 5,
            'value' 		=> $model->getDescription(),
            'wrap'			=> 'soft'
        ));

//        if (!$model->getId() && !Mage::getSingleton('adminhtml/session')->getTagData() ) {
//            $model->setStatus(Mage_Tag_Model_Tag::STATUS_APPROVED);
//        }

//        if ( Mage::getSingleton('adminhtml/session')->getTagData() ) {
//            $form->setValues(Mage::getSingleton('adminhtml/session')->getTagData());
//            Mage::getSingleton('adminhtml/session')->setTagData(null);
//        } else {
//            $form->setValues($model->getData());
//        }

        $form->setUseContainer(true);
        $form->setAction( $form->getAction() . 'ret/' . $this->getRequest()->getParam('ret') );
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
