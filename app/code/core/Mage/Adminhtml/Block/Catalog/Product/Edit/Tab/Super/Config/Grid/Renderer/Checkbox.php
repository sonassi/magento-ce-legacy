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
 * Adminhtml catalog super product link grid checkbox renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid_Renderer_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{
	/**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {	
    	$result = parent::render($row);
    	return $result.'<input type="hidden" class="value-json" value="'.htmlspecialchars($this->getAttributesJson($row)).'" />';
    }
    
    public function getAttributesJson(Varien_Object $row)
    {
    	if(!$this->getColumn()->getAttributes()) {
    		return '[]';
    	}
    	
    	$result = array();
    	foreach($this->getColumn()->getAttributes() as $attribute) {
    		if($attribute->getSourceModel()) {
    			$label = $attribute->getSource()->getOptionText($row->getData($attribute->getAttributeCode()));    			
    		} else {
    			$label = $row->getData($attribute->getAttributeCode());
    		}
    		
    		$item = array();
    		$item['label'] = $label;
    		$item['attribute_id'] = $attribute->getAttributeId();
    		$item['value_index']	= $row->getData($attribute->getAttributeCode());
    		$result[] = $item;
    	}
    	
    	return Zend_Json::encode($result);
    }
}// Class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid_Renderer_Checkbox END