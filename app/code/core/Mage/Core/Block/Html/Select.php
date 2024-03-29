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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTML select element block
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Block_Html_Select extends Mage_Core_Block_Abstract
{
    protected $_options = array();

    public function getOptions()
    {
        return $this->_options;
    }

    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    public function addOption($value, $label, $params=array())
    {
        $this->_options[] = array('value'=>$value, 'label'=>$label);
        return $this;
    }

    public function setId($id)
    {
        $this->setData('id', $id);
        return $this;
    }

    public function setClass($class)
    {
        $this->setData('class', $class);
        return $this;
    }

    public function setTitle($title)
    {
        $this->setData('title', $title);
        return $this;
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function getClass()
    {
        return $this->getData('class');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function toHtml()
    {
		if (!$this->_beforeToHtml()) {
			return '';
		}

        $html = '<select name="'.$this->getName().'" id="'.$this->getId().'" class="'
            .$this->getClass().'" title="'.$this->getTitle().'" '.$this->getExtraParams().'>';
        $values = $this->getValue();

        if (!is_array($values)){
            if (!empty($values)) {
                $values = array($values);
            } else {
                $values = array();
            }
        }

        $isArrayOption = true;
        foreach ($this->getOptions() as $key => $option) {
            if ($isArrayOption && is_array($option)) {
                $value = $option['value'];
                $label = $option['label'];
            }
            else {
                $value = $key;
                $label = $option;
                $isArrayOption = false;
            }
            $selected = in_array($value, $values) ? ' selected' : '';
        	$html.= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
        }
        $html.= '</select>';
        return $html;
    }

    public function getHtml()
    {
        return $this->toHtml();
    }
}
