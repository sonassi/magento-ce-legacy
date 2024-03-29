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
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_Run extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('system/convert/profile/run.phtml');
    }

    public function getRunButtonHtml()
    {
        $html = '';
/*
        if (Mage::registry('current_convert_profile')->getDirection()=='import') {
            $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setLabel($this->__('Upload import file'))
                ->setOnClick('showUpload()')
                ->toHtml();
        }
*/
        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
            ->setClass('save')->setLabel($this->__('Run Profile Inside This Window'))
            ->setOnClick('runProfile()')
            ->toHtml();

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
            ->setClass('save')->setLabel($this->__('Run Profile In Popup'))
            ->setOnClick('runProfile(true)')
            ->toHtml();

        return $html;
    }

    public function getProfileId()
    {
        return Mage::registry('current_convert_profile')->getId();
    }
}
