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
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form select element
 *
 * @category   Varien
 * @package    Varien_Data
 */
class Varien_Data_Form_Element_Select extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('select');
        $this->setExtType('combobox');
        $this->_prepareOptions();
    }

    public function getElementHtml()
    {
        $this->addClass('select');
        $html = '<select id="'.$this->getHtmlId().'" name="'.$this->getName().'" '.$this->serialize($this->getHtmlAttributes()).'>'."\n";

        $value = $this->getValue();
        if (!is_array($value)) {
            $value = array($value);
        }

        if ($values = $this->getValues()) {
            foreach ($values as $option) {
                if (is_array($option['value'])) {
                    $html.='<optgroup label="'.$option['label'].'">'."\n";
                    foreach ($option['value'] as $groupItem) {
                        $html.= $this->_optionToHtml($groupItem, $value);
                    }
                    $html.='</optgroup>'."\n";
                }
                else {
                    $html.= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html.= '</select>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    protected function _optionToHtml($option, $selected)
    {
        $html = '<option value="'.$this->_escape($option['value']).'"';
        $html.= isset($option['title']) ? 'title="'.$option['title'].'"' : '';
        $html.= isset($option['style']) ? 'style="'.$option['style'].'"' : '';
        if (in_array($option['value'], $selected)) {
            $html.= ' selected="selected"';
        }
        $html.= '>'.$option['label']. '</option>'."\n";
        return $html;
    }

    protected function _prepareOptions()
    {
        $values = $this->getValues();
        if (empty($values)) {
            $options = $this->getOptions();
            if (is_array($options)) {
                $values = array();
                foreach ($options as  $value => $label) {
                    $values[] = array('value' => $value, 'label' => $label);
                }
            } elseif (is_string($options)) {
                $values = array( array('value' => $options, 'label' => $options) );
            }
            $this->setValues($values);
        }
    }

}