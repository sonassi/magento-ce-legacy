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
 * Adminhtml customer wishlist grid filter by store
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist_Grid_Filter_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select 
{
	protected function _getOptions()
	{
		$options = Mage::registry('stores_select_collection')->toOptionArray();
		array_unshift($options, array('label'=>'','value'=>''));
		return $options;
	}
	
	public function getCondition()
	{	
		return $this->getValue();
	}
}// Class Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist_Grid_Filter_Store END