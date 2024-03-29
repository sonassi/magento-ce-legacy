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
 * Base html block
 *
 */

class Mage_Core_Block_Text_List extends Mage_Core_Block_Text
{
	function toHtml()
	{
	    $this->setText('');
		foreach ($this->getSortedChildren() as $name) {
			$block = $this->getLayout()->getBlock($name);
			if (!$block) {
				Mage::throwException(Mage::helper('core')->__('Invalid block: %s', $name));
			}
			$this->addText($block->toHtml());
		}
	    return parent::toHtml();
	}
}