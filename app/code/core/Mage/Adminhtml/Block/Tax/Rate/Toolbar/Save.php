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
 * Admin tax rate save toolbar
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Rate_Toolbar_Save extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('createUrl', Mage::getUrl('*/tax_rate/save'));
        $this->setTemplate('tax/toolbar/rate/save.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('backButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('tax')->__('Back'),
                    'onclick'   => 'window.location.href=\''.Mage::getUrl('*/*/').'\'',
                    'class' => 'back'
                ))
        );

        $this->setChild('resetButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('tax')->__('Reset'),
                    'onclick'   => 'window.location.reload()'
                ))
        );

        $this->setChild('saveButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('tax')->__('Save Rate'),
                    'onclick'   => 'wigetForm.submit();return false;',
                    'class' => 'save'
                ))
        );

        $this->setChild('deleteButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('tax')->__('Delete Rate'),
                    'onclick'   => 'deleteConfirm(\'' . Mage::helper('tax')->__('Are you sure you want to do this?') . '\', \'' . Mage::getUrl('*/*/delete', array('rate' => $this->getRequest()->getParam('rate'))) . '\')',
                    'class' => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('resetButton');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('saveButton');
    }

    public function getDeleteButtonHtml()
    {
        if( intval($this->getRequest()->getParam('rate')) == 0 ) {
            return;
        }
        return $this->getChildHtml('deleteButton');
    }
}