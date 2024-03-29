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
 * @package    Varien_Filter
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Template constructions filter
 *
 * @category   Varien
 * @package    Varien_Filter
 */

class Varien_Filter_Template implements Zend_Filter_Interface
{
    /**
     * Cunstruction regular expression
     */
    const CONSTRUCTION_PATTERN = '/{{([a-z]{0,10})(.*?)}}/si';

    /**
     * Cunstruction logic regular expression
     */
    const CONSTRUCTION_DEPEND_PATTERN = '/{{depend\s*(.*?)}}(.*?){{\\/depend\s*}}/si';

    /**
     * Assigned template variables
     *
     * @var array
     */
    protected $_templateVars = array();

    /**
     * Include processor
     *
     * @var array|string|null
     */
    protected $_includeProcessor = null;

    /**
     * Sets template variables that's can be called througth {var ...} statement
     *
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        foreach($variables as $name=>$value) {
            $this->_templateVars[$name] = $value;
        }
        return $this;
    }

    /**
     * Sets the proccessor of includes.
     *
     * @param array $callback it must return string
     */
    public function setIncludeProcessor(array $callback)
    {
        $this->_includeProcessor = $callback;
        return $this;
    }

    /**
     * Sets the proccessor of includes.
     *
     * @return array|null
     */
    public function getIncludeProcessor()
    {
        return is_callable($this->_includeProcessor) ? $this->_includeProcessor : null;
    }

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        // Depend operand should be first
        if(preg_match_all(self::CONSTRUCTION_DEPEND_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach($constructions as $index=>$construction) {
                $replacedValue = '';
                $callback = array($this, 'dependDirective');
                if(!is_callable($callback)) {
                    continue;
                }
                try {
					$replacedValue = call_user_func($callback, $construction);
                } catch (Exception $e) {
                	throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }

        if(preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach($constructions as $index=>$construction) {
                $replacedValue = '';
                $callback = array($this, $construction[1].'Directive');
                if(!is_callable($callback)) {
                    continue;
                }
                try {
					$replacedValue = call_user_func($callback, $construction);
                } catch (Exception $e) {
                	throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }
        return $value;
    }


    public function varDirective($construction)
    {
    	$replacedValue = $this->_getVariable($construction[2], '');
    	return $replacedValue;
    }

    public function includeDirective($construction)
    {
        // Processing of {include template=... [...]} statement
        $includeParameters = $this->_getIncludeParameters($construction[2]);
        if(!isset($includeParameters['template']) or !$this->getIncludeProcessor()) {
            // Not specified template or not seted include processor
            $replacedValue = '{' . __('Error in include processing') . '}';
        } else {
            // Including of template
            $templateCode = $includeParameters['template'];
            unset($includeParameters['template']);
            $includeParameters = array_merge_recursive($includeParameters, $this->_templateVars);
            $replacedValue = call_user_func($this->getIncludeProcessor(), $templateCode, $includeParameters);
        }
        return $replacedValue;
    }

    public function dependDirective($construction)
    {
        if($this->_getVariable($construction[1], '')=='') {
            return '';
        } else {
            return $construction[2];
        }
    }

    /**
     * Return associative array of include construction.
     *
     * @param string $value raw parameters
     * @return array
     */
    protected function _getIncludeParameters($value)
    {
        $tokenizer = new Varien_Filter_Template_Tokenizer_Parameter();
        $tokenizer->setString($value);

        return $tokenizer->tokenize();
    }

     /**
     * Return variable value for var construction
     *
     * @param string $value raw parameters
     * @param string $default default value
     * @return string
     */
    protected function _getVariable($value, $default='{no_value_defined}')
    {
        Varien_Profiler::start("email_template_proccessing_variables");
        $tokenizer = new Varien_Filter_Template_Tokenizer_Variable();
        $tokenizer->setString($value);
        $stackVars = $tokenizer->tokenize();
        $result = $default;
        $last = 0;
        for($i = 0; $i < count($stackVars); $i ++) {
            if ($i == 0 && isset($this->_templateVars[$stackVars[$i]['name']])) {
                // Getting of template value
                $stackVars[$i]['variable'] =& $this->_templateVars[$stackVars[$i]['name']];
            } else if (isset($stackVars[$i-1]['variable'])
                       && $stackVars[$i-1]['variable'] instanceof Varien_Object) {
                // If object calling methods or getting properties
                if($stackVars[$i]['type'] == 'property') {
                    $caller = "get" . uc_words($stackVars[$i]['name'], '');
                    if(is_callable(array($stackVars[$i-1]['variable'], $caller))) {
                        // If specified getter for this property
                        $stackVars[$i]['variable'] = $stackVars[$i-1]['variable']->$caller();
                    } else {
                        $stackVars[$i]['variable'] = $stackVars[$i-1]['variable']
                                                        ->getData($stackVars[$i]['name']);
                    }
                } else if ($stackVars[$i]['type'] == 'method') {
                    // Calling of object method
                    if (is_callable(array($stackVars[$i-1]['variable'], $stackVars[$i]['name'])) || substr($stackVars[$i]['name'],0,3) == 'get') {
                        $stackVars[$i]['variable'] = call_user_func_array(array($stackVars[$i-1]['variable'],
                                                                                $stackVars[$i]['name']),
                                                                          $stackVars[$i]['args']);
                    }
                }
                $last = $i;
            }
        }

        if(isset($stackVars[$last]['variable'])) {
            // If value for construction exists set it
            $result = (string) $stackVars[$last]['variable'];
        }
        Varien_Profiler::stop("email_template_proccessing_variables");
        return $result;
    }
}
