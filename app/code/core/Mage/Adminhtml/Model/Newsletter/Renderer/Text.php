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
 * Template text preview field renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Model_Newsletter_Renderer_Text implements Varien_Data_Form_Element_Renderer_Interface
{
        
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<span class="field-row">'."\n";
        if ($element->getLabel()) {
            $html.= '<label for="'.$element->getHtmlId().'">'.$element->getLabel().'</label>'."\n";
        }
        $html.= '<iframe src="' . $element->getValue() . '" id="' . $element->getHtmlId() . '" frameborder="0" class="template-preview"/>';
        $html.= '</span>'."\n";
        
        return $html;
    }
}