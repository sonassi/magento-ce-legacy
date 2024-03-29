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
 * Customer new password field renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Customer_Edit_Renderer_Newpass extends Mage_Core_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<span class="field-row">';
        $html.= $element->getLabelHtml();
        $html.= $element->getElementHtml();
        $html.= '</span>'."\n";
        $html.= '<span class="field-row">';
        $html.= '<label>&nbsp;</label>';
        $html.= Mage::helper('customer')->__('or');
        $html.= '</span>'."\n";
        $html.= '<span class="field-row">';
        $html.= '<label>&nbsp;</label>';
        $html.= '<input type="checkbox" name="'.$element->getName().'" value="auto" onclick="setElementDisable(\''.$element->getHtmlId().'\', this.checked)"/>&nbsp;';
        $html.= '<label class="normal">'.Mage::helper('customer')->__('Send auto-generated password').'</label>';
        $html.= '</span>'."\n";

        return $html;
    }
}
