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
 * Fieldset config form element renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Config_Form_Fieldset 
    extends Mage_Core_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		$html = $this->_getHeaderHtml($element);
		
        foreach ($element->getElements() as $field) {
        	$html.= $field->toHtml();
        }
        
        $html .= $this->_getFooterHtml($element);

        return $html;
    }
    
    protected function _getHeaderHtml($element)
    {
        $id = $element->getHtmlId();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');

        $html = '<h4 class="icon-head head-edit-form">'.$element->getLegend().'</h4>';
        $html.= '<fieldset class="config" id="'.$element->getHtmlId().'">';
        $html.= '<legend>'.$element->getLegend().'</legend>';
        
        // field label column
        $html.= '<table cellspacing="0"><colgroup class="label"/><colgroup class="value"/>';
        if (!$default) {
            $html.= '<colgroup class="default"/>';
        }
        $html.= '<tbody>';
        
        return $html;
    }
    
    protected function _getFooterHtml($element)
    {
        $html = '</tbody></table></fieldset>';
        return $html;
    }
}
