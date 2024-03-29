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
 * Media library js helper
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Helper_Media_Js extends Mage_Core_Helper_Js
{
    public function __construct()
    {
         $this->_translateData = array(
            'Complete' => $this->__('Complete'),
            'File size should be more than 0 bytes' => $this->__('File size should be more than 0 bytes'),
            'Upload Security Error' => $this->__('Upload Security Error'),
            'Upload HTTP Error'     => $this->__('Upload HTTP Error'),
            'Upload I/O Error'     => $this->__('Upload I/O Error'),
            'Tb' => $this->__('Tb'),
            'Gb' => $this->__('Gb'),
            'Mb' => $this->__('Mb'),
            'Kb' => $this->__('Kb'),
            'b' => $this->__('b')
         );
    }

    /**
     * Retrieve JS translator initialization javascript
     *
     * @return string
     */
    public function getTranslatorScript()
    {
        $script = 'if (!window.Translator) {'
                . '    var Translator = new Translate('.$this->getTranslateJson().');'
                . '} else {'
                . '    Translator.add('.$this->getTranslateJson().');'
                . '}';
        return $this->getScript($script);
    }
} // Class Mage_Adminhtml_Helper_Media_Js End