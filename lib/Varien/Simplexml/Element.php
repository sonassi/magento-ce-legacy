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
 * @package    Varien_Simplexml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Extends SimpleXML to add valuable functionality to SimpleXMLElement class
 *
 * @category   Varien
 * @package    Varien_Simplexml
 */
class Varien_Simplexml_Element extends SimpleXMLElement
{
    /**
     * Would keep reference to parent node
     *
     * If SimpleXMLElement would support complicated attributes
     *
     * @todo make use of spl_object_hash to keep global array of simplexml elements
     *       to emulate complicated attributes
     * @var Varien_Simplexml_Element
     */
    protected $_parent = null;

    /**
     * For future use
     *
     * @param Varien_Simplexml_Element $element
     */
    public function setParent($element)
    {
        #$this->_parent = $element;
    }

    /**
     * Returns parent node for the element
     *
     * Currently using xpath
     *
     * @return Varien_Simplexml_Element
     */
    public function getParent()
    {
        if (!empty($this->_parent)) {
            $parent = $this->_parent;
        } else {
            $arr = $this->xpath('..');
            $parent = $arr[0];
        }
        return $parent;
    }

    /**
     * Find a descendant of a node by path
     *
     * @todo Do we need to make it xpath look-a-like?
     * @todo param string $path Subset of xpath. Example: "child/grand[@attrName='attrValue']/subGrand"
     * @param string $path Example: "child/grand@attrName=attrValue/subGrand" (to make it faster without regex)
     * @return Varien_Simplexml_Element
     */
    public function descend($path)
    {
        #$node = $this->xpath($path);
        #return $node[0];

        $pathArr = explode('/', $path);
        $desc = $this;
        foreach ($pathArr as $nodeName) {
            if (strpos($nodeName, '@')!==false) {
                $a = explode('@', $nodeName);
                $b = explode('=', $a[1]);
                $nodeName = $a[0];
                $attributeName = $b[0];
                $attributeValue = $b[1];
                $found = false;
                foreach ($this->$nodeName as $desc) {
                    if ((string)$nodeChild[$attributeName]===$attributeValue) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $desc = false;
                }
            } else {
                $desc = $desc->$nodeName;
            }
            if (!$desc) {
                return false;
            }
        }
        return $desc;
    }

    /**
     * Returns the node and children as an array
     *
     * @return array
     */
    public function asArray()
    {
    	$r = array();

    	$attributes = $this->attributes();
    	foreach($attributes as $k=>$v) {
    		if ($v) $r['@'][$k] = (string) $v;
    	}

    	if (!($children = $this->children())) {
    		$r = (string) $this;
    		return $r;
    	}

    	foreach($children as $childName=>$child) {
    	    $r[$childName] = array();
    		foreach ($child as $index=>$element) {
    			$r[$childName][$index] = $element->asArray();
    		}
    	}

    	return $r;
	}

	/**
	 * Makes nicely formatted XML from the node
	 *
	 * @param string $filename
	 * @param int|boolean $level if false
	 * @return string
	 */
	public function asNiceXml($filename='', $level=0)
	{
	    if (is_numeric($level)) {
    	    $pad = str_pad('', $level*3, ' ', STR_PAD_LEFT);
    	    $nl = "\n";
	    } else {
	        $pad = '';
	        $nl = '';
	    }

	    $out = $pad.'<'.$this->getName();

	    if ($attributes = $this->attributes()) {
	        foreach ($attributes as $key=>$value) {
	            $out .= ' '.$key.'="'.str_replace('"', '\"', (string)$value).'"';
	        }
	    }

	    if ($children = $this->children()) {
	        $out .= '>'.$nl;
	        foreach ($children as $child) {
	            $out .= $child->asNiceXml('', is_numeric($level) ? $level+1 : true);
	        }
	        $out .= $pad.'</'.$this->getName().'>'.$nl;
	    } else {
	        $value = (string)$this;
	        if (empty($value)) {
	            $out .= '/>'.$nl;
	        } else {
	            $out .= '>'.$this->xmlentities($value).'</'.$this->getName().'>'.$nl;
	        }
	    }

	    if ((0===$level || false===$level) && !empty($filename)) {
	        file_put_contents($filename, $out);
	    }

	    return $out;
	}

	public function innerXml($level=0)
	{
	    $out = '';
	    foreach ($this->children() as $child) {
	        $out .= $child->asNiceXml($level);
	    }
	    return $out;
	}

	/**
	 * Converts meaningful xml characters to xml entities
	 *
	 * @param  string
	 * @return string
	 */
	public function xmlentities($value='')
	{
	    if (empty($value)) {
	        $value = (string)$this;
	    }

        $value = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $value);

	    return $value;
	}

	/**
	 * Appends $source to current node
	 *
	 * @param Varien_Simplexml_Element $source
	 * @return Varien_Simplexml_Element
	 */
    function appendChild($source)
    {
        if ($source->children()) {
            $child = $this->addChild($source->getName());
        } else {
            $child = $this->addChild($source->getName(), (string)$source);
        }
        $child->setParent($this);

        $attributes = $source->attributes();
        foreach ($attributes as $key=>$value) {
            $child->addAttribute($key, (string)$value);
        }

        foreach ($source as $sourceChild) {
            $child->appendChild($sourceChild);
        }
        return $this;
    }

    /**
     * Extends current node with xml from $source
     *
     * If $overwrite is false will merge only missing nodes
     * Otherwise will overwrite existing nodes
     *
     * @param Varien_Simplexml_Element $source
     * @param boolean $overwrite
     * @return Varien_Simplexml_Element
     */
    function extend($source, $overwrite=false)
    {
        if (!$source instanceof Varien_Simplexml_Element) {
            return $this;
        }

        foreach ($source->children() as $child) {
            $this->extendChild($child, $overwrite);
        }

        return $this;
    }

    /**
     * Extends one node
     *
     * @param Varien_Simplexml_Element $source
     * @param boolean $overwrite
     * @return Varien_Simplexml_Element
     */
    function extendChild($source, $overwrite=false)
    {
        // this will be our new target node
        $targetChild = null;

        // name of the source node
        $sourceName = $source->getName();

        // here we have children of our source node
        $sourceChildren = $source->children();

        if (!$sourceChildren) {
            // handle string node
            if (isset($this->$sourceName)) {
                // if target already has children return without regard
                if ($this->$sourceName->children()) {
                    return $this;
                }
                if ($overwrite) {
                    unset($this->$sourceName);
                } else {
                    return $this;
                }
            }

            $targetChild = $this->addChild($sourceName, (string)$source->xmlentities());

            $targetChild->setParent($this);
            foreach ($source->attributes() as $key=>$value) {
                $targetChild->addAttribute($key, $value);
            }
            return $this;
        }

        if (isset($this->$sourceName)) {
            $targetChild = $this->$sourceName;
        }

        if (is_null($targetChild)) {
            // if child target is not found create new and descend
            $targetChild = $this->addChild($sourceName);
            $targetChild->setParent($this);
            foreach ($source->attributes() as $key=>$value) {
                $targetChild->addAttribute($key, $value);
            }
        }

        // finally add our source node children to resulting new target node
        foreach ($sourceChildren as $childKey=>$childNode) {
            $targetChild->extendChild($childNode, $overwrite);
        }

        return $this;
    }
/*
    function extendChildByNode($source, $overwrite=false, $mergeBy='name')
    {
        // this will be our new target node
        $targetChild = null;

        // name of the source node
        $sourceName = $source->getName();

        // here we have children of our source node
        $sourceChildren = $source->children();

        if (!$sourceChildren) {
            // handle string node
            if (isset($this->$sourceName)) {
                if ($overwrite) {
                    unset($this->$sourceName);
                } else {
                    return $this;
                }
            }
            $targetChild = $this->addChild($sourceName, (string)$source);
            foreach ($source->attributes() as $key=>$value) {
                $targetChild->addAttribute($key, $value);
            }
            return $this;
        }

        if (isset($this->$sourceName)) {
            // search for target child with same name subnode as node's name
            if (isset($source->$mergeBy)) {
                foreach ($this->$sourceName as $targetNode) {
                    if (!isset($targetNode->$mergeBy)) {
                        Zend::exception("Can't merge identified node with non identified");
                    }
                    if ((string)$source->$mergeBy==(string)$targetNode->$mergeBy) {
                        $targetChild = $targetNode;
                        break;
                    }
                }
            } else {
                $existsWithId = false;
                foreach ($this->$sourceName as $targetNode) {
                    if (isset($targetNode->$mergeBy)) {
                        Zend::exception("Can't merge identified node with non identified");
                    }
                }
                $targetChild = $this->$sourceName;
            }
        }

        if (is_null($targetChild)) {
            // if child target is not found create new and descend
            $targetChild = $this->addChild($sourceName);
            foreach ($source->attributes() as $key=>$value) {
                $targetChild->addAttribute($key, $value);
            }
        }

        // finally add our source node children to resulting new target node
        foreach ($sourceChildren as $childKey=>$childNode) {
            $targetChild->extendChildByNode($childNode, $overwrite, $mergeBy);
        }

        return $this;
    }

    function extendChildByAttribute($source, $overwrite=false, $mergeBy='name')
    {
        // this will be our new target node
        $targetChild = null;

        // name of the source node
        $sourceName = $source->getName();

        // here we have children of our source node
        $sourceChildren = $source->children();

        if (!$sourceChildren) {
            // handle string node
            if (isset($this->$sourceName)) {
                if ($overwrite) {
                    unset($this->$sourceName);
                } else {
                    return $this;
                }
            }
            $targetChild = $this->addChild($sourceName, (string)$source);
            foreach ($source->attributes() as $key=>$value) {
                $targetChild->addAttribute($key, $value);
            }
            return $this;
        }

        if (isset($this->$sourceName)) {
            // search for target child with same name subnode as node's name
            if (isset($source[$mergeBy])) {
                foreach ($this->$sourceName as $targetNode) {
                    if (!isset($targetNode[$mergeBy])) {
                        Zend::exception("Can't merge identified node with non identified");
                    }
                    if ((string)$source[$mergeBy]==(string)$targetNode[$mergeBy]) {
                        $targetChild = $targetNode;
                        break;
                    }
                }
            } else {
                $existsWithId = false;
                foreach ($this->$sourceName as $targetNode) {
                    if (isset($targetNode[$mergeBy])) {
                        Zend::exception("Can't merge identified node with non identified");
                    }
                }
                $targetChild = $this->$sourceName;
            }
        }

        if (is_null($targetChild)) {
            // if child target is not found create new and descend
            $targetChild = $this->addChild($sourceName);
            foreach ($source->attributes() as $key=>$value) {
                $targetChild->addAttribute($key, $value);
            }
        }

        // finally add our source node children to resulting new target node
        foreach ($sourceChildren as $childKey=>$childNode) {
            $targetChild->extendChildByAttribute($childNode, $overwrite, $mergeBy);
        }

        return $this;
    }
*/
}