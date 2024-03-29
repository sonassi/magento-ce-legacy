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
 * description
 *
 * @category    Mage
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Promo_Catalog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'promo_catalog';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('catalogrule')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('catalogrule')->__('Delete Rule'));

        $this->_addButton('save_apply', array(
        	'class'=>'save',
        	'label'=>Mage::helper('catalogrule')->__('Save and Apply'),
        	'onclick'=>"$('rule_auto_apply').value=1; editForm.submit()",
        ));
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('current_promo_catalog_rule');
        if ($rule->getRuleId()) {
            return Mage::helper('catalogrule')->__("Edit Rule '%s'", $rule->getName());
        }
        else {
            return Mage::helper('catalogrule')->__('New Rule');
        }
    }

}
