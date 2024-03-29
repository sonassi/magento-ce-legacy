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
 * Customer address region field renderer
 *
 */
class Mage_Adminhtml_Block_Customer_Edit_Renderer_Region extends Mage_Core_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if ($country = $element->getForm()->getElement('country_id')) {
            $countryId = $country->getValue();
        }
        else {
            return $element->getDefaultHtml();
        }

        $regionId = $element->getForm()->getElement('region_id')->getValue();

        $html = '<span class="field-row">';
        $element->setClass('input-text');
        $html.= $element->getLabelHtml();
        $html.= $element->getElementHtml();

        $selectName = str_replace('region', 'region_id', $element->getName());
        $selectId   = $element->getHtmlId().'_id';
        $html.= '<select id="'.$selectId.'" name="'.$selectName.'" class="select required-entry" style="display:none">
                <option value="">'.Mage::helper('customer')->__('Please select').'</option>
        </select>';
        $html.= '</span>'."\n";
        $html.= '<script type="text/javascript">
            new regionUpdater("'.$country->getHtmlId().'", "'.$element->getHtmlId().'", "'.$selectId.'", '.$this->helper('directory')->getRegionJson().');
        </script>';
        return $html;
    }

}
