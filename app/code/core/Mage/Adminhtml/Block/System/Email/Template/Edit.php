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
 * Adminhtml system template edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_System_Email_Template_Edit extends Mage_Adminhtml_Block_Widget
{
    protected $_template;
    
    public function __construct() 
    {
        parent::__construct();
        $this->setTemplate('system/email/template/edit.phtml');
        $this->_template = Mage::getModel('core/email_template');
        if ($templateId = (int) $this->_request->getParam('id')) {
            $this->_template->load($templateId);
        }
    }
    
    protected function _prepareLayout() 
    {
        $this->setChild('back_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Back'),
                        'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                        'class'   => 'back'
                    )
                )
        );
        
        
        $this->setChild('reset_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Reset'),
                        'onclick' => 'window.location.href = window.location.href'
                    )
                )
        );
        
        
        $this->setChild('delete_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Delete Template'),
                        'onclick' => 'templateControl.deleteTemplate();',
                        'class'   => 'delete'
                    )
                )
        );
        
        $this->setChild('to_plain_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Convert to Plain Text'),
                        'onclick' => 'templateControl.stripTags();',
                        'id'      => 'convert_button'
                    )
                )
        );
        
        
        $this->setChild('to_html_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Return Html Version'),
                        'onclick' => 'templateControl.unStripTags();',
                        'id'      => 'convert_button_back',
                        'style'   => 'display:none'
                    )
                )
        );

        $this->setChild('toggle_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Toggle Editor'),
                        'onclick' => 'templateControl.toggleEditor();',
                        'id'      => 'toggle_button'
                    )
                )
        );

        
        $this->setChild('preview_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Preview Template'),
                        'onclick' => 'templateControl.preview();'                       
                    )
                )
        );

        $this->setChild('save_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Save Template'),
                        'onclick' => 'templateControl.save();',
                        'class'   => 'save'                 
                    )
                )
        );
        return parent::_prepareLayout();
    }
    
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }
    
    public function getToggleButtonHtml()
    {
        return $this->getChildHtml('toggle_button');
    }
    
    
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }
    
    public function getToPlainButtonHtml()
    {
        return $this->getChildHtml('to_plain_button');
    }
    
    public function getToHtmlButtonHtml()
    {
        return $this->getChildHtml('to_html_button');
    }
    
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }
    
    public function getPreviewButtonHtml()
    {
        return $this->getChildHtml('preview_button');
    }
    
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
    
    /**
     * Set edit flag for block
     * 
     * @param boolean $value
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit
     */
    public function setEditMode($value=true)
    {
        $this->_editMode = $value;
        return $this;
    }
    
    /**
     * Return edit flag for block
     *
     * @return boolean
     */
    public function getEditMode()
    {
        return $this->_editMode;
    }
    
    /**
     * Return header text for form
     *
     * @return string
     */
    public function getHeaderText() 
    {
        if($this->getEditMode()) {
          return Mage::helper('adminhtml')->__('Edit Email Template');
        }
        
        return  Mage::helper('adminhtml')->__('New Email Template');
    }
    
       
    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getForm() 
    {
        return $this->getLayout()->createBlock('adminhtml/system_email_template_edit_form')
            ->renderPrepare($this->_template)
            ->toHtml();
    }
    
    /**
     * Return action url for form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return Mage::getUrl('*/*/save');
    }
    
    /**
     * Return preview action url for form
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return Mage::getUrl('*/*/preview');
    }
    
    public function isTextType()
    {
        return $this->_template->isPlain();
    }
    
    /**
     * Return delete url for customer group
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return Mage::getUrl('*/*/delete', array('id' => $this->_request->getParam('id')));
    }
       
   
}
