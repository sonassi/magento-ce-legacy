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
 * Adminhtml newsletter template preview block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
 
class Mage_Adminhtml_Block_Newsletter_Template_Preview extends Mage_Adminhtml_Block_Widget 
{
    public function toHtml() 
    {
        $template = Mage::getModel('newsletter/template');
        if($id = (int)$this->getRequest()->getParam('id')) {
            $template->load($id);
        } else { 
            $template->setTemplateType($this->getRequest()->getParam('type'));
            $template->setTemplateText($this->getRequest()->getParam('text'));    
        }
        
        Varien_Profiler::start("email_template_proccessing");
        $vars = array();
        
        if($this->getRequest()->getParam('subscriber')) {
        	$vars['subscriber'] = Mage::getModel('newsletter/subscriber')
        		->load($this->getRequest()->getParam('subscriber'));
        }
        
        $templateProcessed = $template->getProcessedTemplate($vars, true);
        
        if($template->isPlain()) {
            $templateProcessed = "<pre>" . htmlspecialchars($templateProcessed) . "</pre>";
        }
        
        Varien_Profiler::stop("email_template_proccessing");
        
        return $templateProcessed;
    }
}