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
 * Form fieldset
 *
 * @category   Varien
 * @package    Varien_Data
 */
class Varien_Data_Form_Element_Fieldset extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->_renderer = Varien_Data_Form::getFieldsetRenderer();
        $this->setType('fieldset');
    }

    public function getElementHtml()
    {
        $html = '<fieldset id="'.$this->getHtmlId().'"'.$this->serialize(array('class')).'>'."\n";
        if ($this->getLegend()) {
            $html.= '<legend>'.$this->getLegend().'</legend>'."\n";
        }
        $html.= $this->getChildrenHtml();
        $html.= '</fieldset></div>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getChildrenHtml()
    {
        $html = '';
        foreach ($this->getElements() as $element) {
        	$html.= $element->toHtml();
        }
        return $html;
    }

    public function getDefaultHtml()
    {
        $html = '<div><h4 class="icon-head head-edit-form fieldset-legend">'.$this->getLegend().'</h4>'."\n";
        $html.= $this->getElementHtml();
        return $html;
    }
}