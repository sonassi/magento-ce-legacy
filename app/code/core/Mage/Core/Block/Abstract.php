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
 * Base Content Block class
 *
 * For block generation you must define Data source class, data source class method,
 * parameters array and block template
 *
 */

abstract class Mage_Core_Block_Abstract extends Varien_Object
{
    /**
     * Block name in layout
     *
     * @var string
     */
    protected $_name;

    /**
     * Parent layout of the block
     *
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * Parent block
     *
     * @var Mage_Core_Block_Abstract
     */
    protected $_parent;

    /**
     * Short alias of this block to be refered from parent
     *
     * @var string
     */
    protected $_alias;

    /**
     * Suffix for name of anonymous block
     *
     * @var string
     */
    protected $_anonSuffix;

    /**
     * Contains references to child block objects
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Sorted children list
     *
     * @var array
     */
    protected $_sortedChildren = array();

    /**
     * Children blocks HTML cache array
     *
     * @var array
     */
    protected $_childrenHtmlCache = array();

    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Messages block instance
     *
     * @var Mage_Core_Block_Messages
     */
    protected $_messagesBlock = null;

    /**
     * Whether this block was not explicitly named
     *
     * @var boolean
     */
    protected $_isAnonymous = false;

    /**
     * Parent block
     *
     * @var Mage_Core_Block_Abstract
     */
    protected $_parentBlock;

    protected static $_urlModel;

    public function __construct($attributes=array())
    {
        parent::__construct($attributes);

        if (Mage::registry('controller')) {
            $this->_request = Mage::registry('controller')->getRequest();
        }
        else {
            throw new Exception(Mage::helper('core')->__("Can't retrieve request object"));
        }
    }

    protected function _construct() {}

    /**
     * Retrieve request object
     *
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve parent block
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getParentBlock()
    {
        return $this->_parentBlock;
    }

    /**
     * Set parent block
     *
     * @param   Mage_Core_Block_Abstract $block
     * @return  Mage_Core_Block_Abstract
     */
    public function setParentBlock(Mage_Core_Block_Abstract $block)
    {
        $this->_parentBlock = $block;
        return $this;
    }

    /**
     * Retrieve current action object
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function getAction()
    {
        return Mage::registry('action');
    }

    /**
     * Set layout object
     *
     * @param   Mage_Core_Model_Layout $layout
     * @return  Mage_Core_Block_Abstract
     */
    public function setLayout(Mage_Core_Model_Layout $layout)
    {
        $this->_layout = $layout;
        $this->_prepareLayout();
        return $this;
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changin layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * Retrieve layout object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    public function getIsAnonymous()
    {
        return $this->_isAnonymous;
    }

    public function setIsAnonymous($flag)
    {
        $this->_isAnonymous = $flag;
        return $this;
    }

    public function getAnonSuffix()
    {
        return $this->_anonSuffix;
    }

    public function setAnonSuffix($suffix)
    {
        $this->_anonSuffix = $suffix;
        return $this;
    }

    public function getBlockAlias()
    {
        return $this->_alias;
    }

    public function setBlockAlias($alias)
    {
        $this->_alias = $alias;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        if (!empty($this->_name) && $this->getLayout()) {
            $this->getLayout()
            ->unsetBlock($this->_name)
            ->setBlock($name, $this);
        }
        $this->_name = $name;
        return $this;
    }

    public function getSortedChildren()
    {
        return $this->_sortedChildren;
    }

    /**
     * Retrieve block attribute value
     *
     * Wrapper for method "getData"
     *
     * @param   string $name
     * @return  mixed
     */
    public function getAttribute($name)
    {
        return $this->getData($name);
    }

    /**
     * Set block attribute value
     *
     * Wrapper for method "setData"
     *
     * @param   string $name
     * @param   mixed $value
     * @return  Mage_Core_Block_Abstract
     */
    public function setAttribute($name, $value=null)
    {
        return $this->setData($name, $value);
    }

    /**
     * Set child block
     *
     * @param   string $name
     * @param   Mage_Core_Block_Abstract $block
     * @return  Mage_Core_Block_Abstract
     */
    public function setChild($alias, $block)
    {
        if (is_string($block)) {
            $block = $this->getLayout()->getBlock($block);
            if (!$block) {
                Mage::throwException(Mage::helper('core')->__('Invalid block name to set child %s: %s', $alias, $block));
            }
        }

        if ($block->getIsAnonymous()) {

            $suffix = $block->getAnonSuffix();
            if (empty($suffix)) {
                $suffix = 'child'.sizeof($this->_children);
            }
            $blockName = $this->getNameInLayout().'.'.$suffix;

            if ($this->getLayout()) {
                $this->getLayout()
                ->unsetBlock($block->getNameInLayout())
                ->setBlock($blockName, $block);
            }

            $block->setNameInLayout($blockName);
            $block->setIsAnonymous(false);

            if (empty($alias)) {
                $alias = $blockName;
            }
        }

        $block->setParentBlock($this);
        $block->setBlockAlias($alias);

        $this->_children[$alias] = $block;

        return $this;
    }

    /**
     * Unset child block
     *
     * @param   string $name
     * @return  Mage_Core_Block_Abstract
     */
    public function unsetChild($alias)
    {
        if (isset($this->_children[$alias])) {
            unset($this->_children[$alias]);
        }

        if (!empty($this->_sortedChildren)) {
            $key = array_search($alias, $this->_sortedChildren);
            if (!empty($key)) {
                unset($this->_sortedChildren[$key]);
            }
        }

        return $this;
    }

    /**
     * Unset all children blocks
     *
     * @return Mage_Core_Block_Abstract
     */
    public function unsetChildren()
    {
        $this->_children = array();
        $this->_sortedChildren = array();
        return $this;
    }

    /**
     * Retrieve child block by name
     *
     * @param  string $name
     * @return mixed
     */
    public function getChild($name='')
    {
        if (''===$name) {
            return $this->_children;
        } elseif (isset($this->_children[$name])) {
            return $this->_children[$name];
        }
        return false;
    }

    /**
     * Retrieve child block HTML
     *
     * @param   string $name
     * @param   boolean $useCache
     * @return  string
     */
    public function getChildHtml($name='', $useCache=true)
    {
        if ('' === $name) {
            $children = $this->getChild();

            $out = '';
            foreach ($children as $child) {
                $out .= $this->_getChildHtml($child->getBlockAlias(), $useCache);
            }
            return $out;
        } else {
            return $this->_getChildHtml($name, $useCache);
        }
    }

    /**
     * Retrieve child block HTML
     *
     * @param   string $name
     * @param   boolean $useCache
     * @return  string
     */
    protected function _getChildHtml($name, $useCache=true)
    {
        if ($useCache && isset($this->_childrenHtmlCache[$name])) {
            return $this->_childrenHtmlCache[$name];
        }

        $child = $this->getChild($name);
        if (!$child) {
            $html = '';
        } else {
            $this->_beforeChildToHtml($name, $child);
            $html = $child->toHtml();
        }

        $this->_childrenHtmlCache[$name] = $html;
        return $html;
    }

    /**
     * Prepare child block before generate html
     *
     * @param   string $name
     * @param   Mage_Core_Block_Abstract $child
     */
    protected function _beforeChildToHtml($name, $child) {}

    /**
     * Retrieve block html
     *
     * @param   string $name
     * @return  string
     */
    public function getBlockHtml($name)
    {
        if (!($layout = $this->getLayout()) && !($layout = Mage::registry('action')->getLayout())) {
            return '';
        }
        if (!($block = $layout->getBlock($name))) {
            return '';
        }
        return $block->toHtml();
    }

    /**
     * Insert child block
     *
     * @param   Mage_Core_Block_Abstract $block
     * @param   string $siblingName
     * @param   boolean $after
     * @param   string $alias
     * @return  object $this
     */
    function insert($block, $siblingName='', $after=false, $alias='')
    {
        if ($block->getIsAnonymous()) {
            $this->setChild('', $block);
            $name = $block->getNameInLayout();
        } elseif ('' != $alias) {
            $this->setChild($alias, $block);
            $name = $block->getNameInLayout();
        } else {
            $name = $block->getNameInLayout();
            $this->setChild($name, $block);
        }

        if (''===$siblingName) {
            if ($after) {
                array_push($this->_sortedChildren, $name);
            }
            else {
                array_unshift($this->_sortedChildren, $name);
            }
        } else {
            $key = array_search($siblingName, $this->_sortedChildren);
            if (false!==$key) {
                if ($after) {
                    $key++;
                }
                array_splice($this->_sortedChildren, $key, 0, $name);
            } else {
                if ($after) {
                    array_push($this->_sortedChildren, $name);
                }
                else {
                    array_unshift($this->_sortedChildren, $name);
                }
            }
        }

        return $this;
    }

    /**
     * Append child block
     *
     * @param   Mage_Core_Block_Abstract $block
     * @param   string $alias
     * @return  Mage_Core_Block_Abstract
     */
    function append($block, $alias='')
    {
        $this->insert($block, '', true, $alias);
        return $this;
    }

    /**
     * Before rendering html
     *
     * If returns false html is rendered empty
     *
     * @return boolean
     */
    protected function _beforeToHtml()
    {
        if (Mage::getStoreConfig('advanced/modules_disable_output/'.$this->getModuleName())) {
            return false;
        }
        return true;
    }


    public function toHtml()
    {

    }

    /**
     * Generate url by action data and parameters
     *
     * @param   string $params
     * @param   array $params2
     * @return  string
     */
    public function getUrl($params='', $params2=array())
    {
        return Mage::getUrl($params, $params2);
        #return Mage::registry('controller')->getUrl($params, $params2);
        if (!self::$_urlModel) {
            self::$_urlModel = Mage::getModel('core/url');
        }
        return self::$_urlModel->getUrl($params, $params2);
    }

    public function getUrlBase64($params='', $params2=array())
    {
        return base64_encode($this->getUrl($params, $params2));
    }

    /**
     * Retrieve url of skins file
     *
     * @param   string $file path to file in skin
     * @param   array $params
     * @return  string
     */
    public function getSkinUrl($file=null, array $params=array())
    {
        return Mage::getDesign()->getSkinUrl($file, $params);
    }

    /**
     * Retrieve messages block
     *
     * @return Mage_Core_Block_Messages
     */
    public function getMessagesBlock()
    {
        if (is_null($this->_messagesBlock)) {
            return $this->getLayout()->getMessagesBlock();
        }
        return $this->_messagesBlock;
    }

    /**
     * Set messages block
     *
     * @param   Mage_Core_Block_Messages $block
     * @return  Mage_Core_Block_Abstract
     */
    public function setMessagesBlock(Mage_Core_Block_Messages $block)
    {
        $this->_messagesBlock = $block;
        return $this;
    }

    public function getHelper($type)
    {
        return $this->getLayout()->getHelper($type);
        //return $this->helper($type);
    }

    public function helper($name)
    {
        return $this->getLayout()->helper($name);
    }

    /**
     * Retrieve formating date
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatDate($date=null, $format='short', $showTime=false)
    {
        return $this->helper('core')->formatDate($date, $format, $showTime);
    }

    /**
     * Retrieve module name of block
     *
     * @return string
     */
    public function getModuleName()
    {
        $module = $this->getData('module_name');
        if (is_null($module)) {
            $class = get_class($this);
            $module = substr($class, 0, strpos($class, '_Block'));
            $this->setData('module_name', $module);
        }
        return $module;
    }

    /**
     * Translate block sentence
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->getModuleName());
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }


    public function getCacheKey()
    {
        if (!$this->hasData('cache_key')) {
            $this->setCacheKey($this->getNameInLayout());
        }
        return $this->getData('cache_key');
    }

    public function getCacheTags()
    {
        if (!$this->hasData('cache_tags')) {
            $tags = array();
        } else {
            $tags = $this->getData('cache_tags');
        }
        $tags[] = 'block_html';
        return $tags;
    }

    public function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            return null;
        }
        return $this->getData('cache_lifetime');
    }

    protected function _loadCache()
    {
        if (is_null($this->getCacheLifetime()) || !Mage::app()->useCache('block_html')) {
            return false;
        }
        return Mage::app()->loadCache($this->getCacheKey());
    }

    protected function _saveCache($data)
    {
        if (is_null($this->getCacheLifetime()) || !Mage::app()->useCache('block_html')) {
            return false;
        }
        Mage::app()->saveCache($data, $this->getCacheKey(), $this->getCacheTags(), $this->getCacheLifetime());
        return $this;
    }

    /**
     * Retirve escaped data
     *
     * @param   mixed $data
     * @return  mixed
     */
    public function htmlEscape($data)
    {
        return $this->helper('core')->htmlEscape($data);
    }

}
