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
 * Template model
 *
 * Example:
 *
 * // Loading of template
 * $emailTemplate  = Mage::getModel('core/email_template')
 *    ->load(Mage::getStoreConfig('path_to_email_template_id_config'));
 * $variables = array(
 *    'someObject' => Mage::getSingleton('some_model')
 *    'someString' => 'Some string value'
 * );
 * $emailTemplate->send('some@domain.com', 'Name Of User', $variables);
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Email_Template extends Varien_Object
{
    /**
     * Types of template
     */
    const TYPE_TEXT = 1;
    const TYPE_HTML = 2;

    protected $_templateFilter;
    protected $_preprocessFlag = false;
    protected $_mail;

    /**
     * Configuration of desing package for template
     *
     * @var Varien_Object
     */
    protected $_designConfig;

    /**
     * Return resource of template model.
     *
     * @return Mage_Newsletter_Model_Mysql4_Template
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('core/email_template');
    }

    /**
     * Retrieve mail object instance
     *
     * @return Zend_Mail
     */
    public function getMail()
    {
        if (is_null($this->_mail)) {
            $this->_mail = new Zend_Mail('utf-8');
        }
        return $this->_mail;
    }

    public function getTemplateFilter()
    {
    	if (empty($this->_templateFilter)) {
    		$this->_templateFilter = Mage::getModel('core/email_template_filter');
    		$this->_templateFilter->setUseAbsoluteLinks($this->getUseAbsoluteLinks());
    	}
    	return $this->_templateFilter;
    }

    /**
     * Load template by id
     *
     * @param   int $templateId
     * return   Mage_Newsletter_Model_Template
     */
    public function load($templateId)
    {
        $this->addData($this->getResource()->load($templateId));
        return $this;
    }

    /**
     * Load template by code
     *
     * @param   string $templateCode
     * return   Mage_Newsletter_Model_Template
     */
    public function loadByCode($templateCode)
    {
        $this->addData($this->getResource()->loadByCode($templateCode));
        return $this;
    }

    /**
     * Return template id
     * return int|null
     */
    public function getId()
    {
        return $this->getTemplateId();
    }

    /**
     * Set id of template
     * @param int $value
     */
    public function setId($value)
    {
        return $this->setTemplateId($value);
    }

    /**
     * Return true if this template can be used for sending queue as main template
     *
     * @return boolean
     */
    public function isValidForSend()
    {
        return $this->getSenderName() && $this->getSenderEmail() && $this->getTemplateSubject();
    }

    /**
     * Return true if template type eq text
     *
     * @return boolean
     */
    public function isPlain()
    {
        return $this->getTemplateType() == self::TYPE_TEXT;
    }

    /**
     * Save template
     */
    public function save()
    {
        $this->getResource()->save($this);
        return $this;
    }


    public function getProcessedTemplate(array $variables = array())
    {
        $processor = $this->getTemplateFilter();

        if(!$this->_preprocessFlag) {
        	$variables['this'] = $this;
        }

        $processor
            ->setIncludeProcessor(array($this, 'getInclude'))
            ->setVariables($variables);


        $this->_applyDesignConfig();
        $processedResult = $processor->filter($this->getTemplateText());
        $this->_cancelDesignConfig();
        return $processedResult;
    }



    public function getInclude($template, array $variables)
    {
        $thisClass = __CLASS__;
        $includeTemplate = new $thisClass();

        $includeTemplate->loadByCode($template);

        return $includeTemplate->getProcessedTemplate($variables);
    }

    /**
     * Send mail to recipient
     *
     * @param   string      $email		  E-mail
     * @param   string|null $name         receiver name
     * @param   array       $variables    template variables
     * @return boolean
     **/
    public function send($email, $name=null, array $variables = array())
    {
        if(!$this->isValidForSend()) {
            return false;
        }

        if (is_null($name)) {
            $name = substr($email, 0, strpos($email, '@'));
        }

        $variables['email'] = $email;
        $variables['name'] = $name;

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();
        $mail->addTo($email, $name);

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject($this->getProcessedTemplateSubject($variables));
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
        try {
            $mail->send(); // Zend_Mail warning..
            $this->_mail = null;
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null)
    {
    	if (is_null($storeId)) {
    		$storeId = Mage::app()->getStore()->getId();
    	}
    	/*$templateId = Mage::getStoreConfig("trans_email/trans_{$transCode}/template", $storeId);
    	$identity = Mage::getStoreConfig("trans_email/trans_{$transCode}/identity", $storeId);*/

    	$this->load($templateId);
    	if (!$this->getId()) {
    		throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid transactional email code'));
    	}
    	$this->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name', $storeId));
    	$this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $storeId));
    	$this->send($email, $name, $vars);
    	return $this;
	}

    /**
     * Delete template from DB
     */
    public function delete()
    {
        $this->getResource()->delete($this->getId());
        $this->setId(null);
        return $this;
    }


    public function getProcessedTemplateSubject(array $variables)
    {
    	$processor = $this->getTemplateFilter();

        if(!$this->_preprocessFlag) {
        	$variables['this'] = $this;
        }

        $processor->setVariables($variables);

        $this->_applyDesignConfig();
        $processedResult = $processor->filter($this->getTemplateSubject());
        $this->_cancelDesignConfig();
        return $processedResult;
    }

    public function setDesignConfig(array $config)
    {
        if(is_null($this->_designConfig)) {
            $this->_designConfig = new Varien_Object();
        }
        $this->getDesignConfig()->setData($config);
        return $this;
    }

    public function getDesignConfig()
    {
        return $this->_designConfig;
    }

    protected function _applyDesignConfig()
    {
        if ($this->getDesignConfig()) {
            $this->getDesignConfig()
                ->setOldArea(Mage::getDesign()->getArea())
                ->setOldStore(Mage::getDesign()->getStore());

            if ($this->getDesignConfig()->getArea()) {
                Mage::getDesign()->setArea($this->getDesignConfig()->getArea());
            }

            if ($this->getDesignConfig()->getStore()) {
                Mage::getDesign()->setStore($this->getDesignConfig()->getStore());
            }
        }
        return $this;
    }


    protected function _cancelDesignConfig()
    {
        if ($this->getDesignConfig()) {
            if ($this->getDesignConfig()->getOldArea()) {
                Mage::getDesign()->setArea($this->getDesignConfig()->getOldArea());
            }

            if ($this->getDesignConfig()->getOldStore()) {
                Mage::getDesign()->setStore($this->getDesignConfig()->getOldStore());
            }
        }
        return $this;
    }
}
