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
 * @package    Mage_Rule
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Rule_Model_Rule extends Mage_Core_Model_Abstract
{
    protected $_conditions;
    protected $_actions;
    protected $_form;
    
    protected function _construct()
    {
    	$this->_init('rule/rule');
        parent::_construct();
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('rule/condition_combine');
    }
    
    public function _resetConditions(Mage_Rule_Model_Condition_Interface $conditions=null)
    {
        if (is_null($conditions)) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1');
        $this->setConditions($conditions);

        return $this;
    }
    
    public function setConditions(Mage_Rule_Model_Condition_Interface $conditions)
    {
        $this->_conditions = $conditions;
        return $this;
    }
    
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }
        return $this->_conditions;
    }
    
    public function getActionsInstance()
    {
        return Mage::getModel('rule/action_collection');
    }
    
    public function _resetActions(Mage_Rule_Model_Action_Interface $actions=null)
    {
        if (is_null($actions)) {
            $actions = $this->getActionsInstance();
        }
        $actions->setRule($this)->setId('1');
        $this->setActions($actions);
        
        return $this;
    }
    
    public function setActions(Mage_Rule_Model_Action_Interface $actions)
    {
        $this->_actions = $actions;
        return $this;
    }
    
    public function getActions()
    {
        if (!$this->_actions) {
            $this->_resetActions();
        }
        return $this->_actions;
    }
    
    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
        }
        return $this->_form;
    }

    public function asString($format='')
    {
        $str = Mage::helper('rule')->__("Name: %s", $this->getName()) ."\n"
             . Mage::helper('rule')->__("Start at: %s", $this->getStartAt()) ."\n"
             . Mage::helper('rule')->__("Expire at: %s", $this->getExpireAt()) ."\n"
             . Mage::helper('rule')->__("Description: %s", $this->getDescription()) ."\n\n"
             . $this->getConditions()->asStringRecursive() ."\n\n"
             . $this->getActions()->asStringRecursive() ."\n\n";
        return $str;
    }    
    
    public function asHtml()
    {
        $str = Mage::helper('rule')->__("Name: %s", $this->getName()) ."<br>"
             . Mage::helper('rule')->__("Start at: %s", $this->getStartAt()) ."<br>"
             . Mage::helper('rule')->__("Expire at: %s", $this->getExpireAt()) ."<br>"
             . Mage::helper('rule')->__("Description: %s", $this->getDescription()) .'<br>'
             . '<ul class="rule-conditions">'.$this->getConditions()->asHtmlRecursive().'</ul>'
             . '<ul class="rule-actions">'.$this->getActions()->asHtmlRecursive()."</ul>";
        return $str;
    }
    
    public function loadPost(array $rule)
    {
    	$arr = array();
    	foreach ($rule as $key=>$value) {
    	    if ($key==='conditions' || $key==='actions') {
    	    	foreach ($value as $id=>$data) {
    	    		$path = explode('.', $id);
    	    		$node =& $arr;
    	    		for ($i=0, $l=sizeof($path); $i<$l; $i++) {
    	    			if (!isset($node[$key][$path[$i]])) {
    	    				$node[$key][$path[$i]] = array();
    	    			}
    	    			$node =& $node[$key][$path[$i]];
    	    		}
    	    		foreach ($data as $k=>$v) {
    	    			$node[$k] = $v;
    	    		}
    	    	}
    	    } else {
    	        $this->setData($key, $value);
    	    }
    	}
#echo "<pre>".print_r($rule,1)."</pre>";
#echo "<pre>".print_r($arr,1)."</pre>";
		if (isset($arr['conditions'])) {
    		$this->getConditions()->loadArray($arr['conditions'][1]);
		}
		if (isset($arr['actions'])) {
    		$this->getActions()->loadArray($arr['actions'][1]);
		}
#echo "<pre>".print_r($this->getConditions()->asArray(),1)."</pre>"; die;

    	return $this;
    }
    
    /**
     * Returns rule as an array for admin interface
     * 
     * Output example:
     * array(
     *   'name'=>'Example rule',
     *   'conditions'=>{condition_combine::asArray}
     *   'actions'=>{action_collection::asArray}
     * )
     * 
     * @return array
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = array(
            'name'=>$this->getName(),
            'start_at'=>$this->getStartAt(),
            'expire_at'=>$this->getExpireAt(),
            'description'=>$this->getDescription(),
            'conditions'=>$this->getConditions()->asArray(),
            'actions'=>$this->getActions()->asArray(),
        );
        
        return $out;
    }

    public function validate(Varien_Object $object)
    {
        return $this->getConditions()->validate($object);
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
		$conditionsArr = unserialize($this->getConditionsSerialized());
		if (!empty($conditionsArr) && is_array($conditionsArr)) {
            $this->getConditions()->loadArray($conditionsArr);
		}
        
        $actionsArr = unserialize($this->getActionsSerialized());
        if (!empty($actionsArr) && is_array($actionsArr)) {
            $this->getActions()->loadArray($actionsArr);
        }
        
        $this->setStoreIds(explode(',',$this->getStoreIds()));
        $this->setCustomerGroupIds(explode(',',$this->getCustomerGroupIds()));
    }

    protected function _beforeSave()
    {
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }

        if ($this->getActions()) {
            $this->setActionsSerialized(serialize($this->getActions()->asArray()));
            $this->unsActions();
        }
        if (is_array($this->getStoreIds())) {
            $this->setStoreIds(join(',', $this->getStoreIds()));
        }
        if (is_array($this->getCustomerGroupIds())) {
            $this->setCustomerGroupIds(join(',', $this->getCustomerGroupIds()));
        }
        parent::_beforeSave();
    }
}
