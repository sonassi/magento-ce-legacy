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
 * Base widget class
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Widget extends Mage_Core_Block_Template
{
    public function getId()
    {
        if ($this->getData('id')===null) {
            $this->setData('id', 'id_'.md5(time()));
        }
        return $this->getData('id');
    }

    public function getHtmlId()
    {
        return $this->getId();
    }

    public function getCurrentUrl($params=array())
    {
        return Mage::getUrl('*/*/*', array('_current'=>true));
    }

    protected function _addBreadcrumb($label, $title=null, $link=null)
    {
        $this->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
    }

    public function getButtonHtml($label, $onclick, $class='') {
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array('label' => $label, 'onclick' => $onclick, 'class' => $class))->toHtml();
    }
    
    public function getGlobalIcon()
    {
        return '<img src="'.$this->getSkinUrl('images/fam_link.gif').'" alt="'.$this->__('Global Attribute').'" title="'.$this->__('This attribute shares the same value in all the stores').'" class="attribute-global"/>';
    }
}
