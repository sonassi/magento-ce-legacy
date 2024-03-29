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
class Mage_Core_Block_Html_Date extends Mage_Core_Block_Template
{
    public function toHtml()
    {
        $html  = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'value="'.$this->getValue().'" class="'.$this->getClass().'" style="width:100px" /> ';

        $html .= '<img src="' . $this->getImage() . '" alt="" align="absmiddle" ';
        $html .= 'title="' . $this->helper('core')->__('Select Date') . '" id="' . $this->getId() . '_trig" />';

        $html .=

        '<script type="text/javascript">
            Calendar.setup({
                inputField  : "' . $this->getId() . '",
                ifFormat    : "' . $this->getFormat() . '",
                button      : "' . $this->getId() . '_trig",
                align       : "Bl",
                singleClick : true
            });
        </script>';


        return $html;
    }

    public function getEscapedValue($index=null) {

        if($this->getFormat() && $this->getValue()) {
            return strftime($this->getFormat(), strtotime($this->getValue()));
        }

        return htmlspecialchars($this->getValue());
    }

    public function getHtml()
    {
        return $this->toHtml();
    }

}
