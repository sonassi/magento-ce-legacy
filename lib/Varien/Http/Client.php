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
 * @package    Varien_Http
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien HTTP Client
 *
 * @category   Varien
 * @package    Varien_Http
 */
class Varien_Http_Client extends Zend_Http_Client
{
    public function __construct($uri = null, $config = null)
    {
        $this->config['useragent'] = 'Varien_Http_Client';
        
        parent::__construct($uri, $config);
    }
    
    protected function _trySetCurlAdapter()
    {
        if (extension_loaded('curl')) {
            $this->setAdapter(new Varien_Http_Adapter_Curl());
        }
        return $this;
    }
    
    public function request($method = null)
    {
        $this->_trySetCurlAdapter();
        return parent::request($method);
    }
}
