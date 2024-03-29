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


class Mage_Core_Block_Text_Tag_Meta extends Mage_Core_Block_Text
{
    public function toHtml()
    {
        if (!$this->getContentType()) {
            $this->setContentType('text/html; charset=utf-8');
        }
        $this->addText('<meta http-equiv="Content-Type" content="'.$this->getContentType().'"/>'."\n");
        $this->addText('<title>'.$this->getTitle().'</title>'."\n");
        $this->addText('<meta name="title" content="'.$this->getTitle().'"/>'."\n");
        $this->addText('<meta name="description" content="'.$this->getDescription().'"/>'."\n");
        $this->addText('<meta name="keywords" content="'.$this->getKeywords().'"/>'."\n");
        $this->addText('<meta name="robots" content="'.$this->getRobots().'"/>'."\n");
        
        return parent::toHtml();
    }
}