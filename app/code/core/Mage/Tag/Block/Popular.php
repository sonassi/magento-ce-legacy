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
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Popular tags block
 *
 * @category   Mage
 * @package    Mage_Tag
 */

class Mage_Tag_Block_Popular extends Mage_Core_Block_Template
{

    protected $_tags;
    protected $_minPopularity;
    protected $_maxPopularity;

    protected function _loadTags()
    {
        if (empty($this->_tags)) {
            $this->_tags = array();
            $tags = Mage::getResourceModel('tag/tag_collection')
                ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
                ->addPopularity()
                ->setActiveFilter()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder('popularity', 'DESC')
                ->limit(20)
                ->load()
                ->getItems();

            if( count($tags) == 0 ) {
                return $this;
            }


            $this->_maxPopularity = reset($tags)->getPopularity();
            $this->_minPopularity = end($tags)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = ( $range == 0 ) ? 1 : $range;
            foreach ($tags as $tag) {
                if( !$tag->getPopularity() ) {
                    continue;
                }
                $tag->setRatio(($tag->getPopularity()-$this->_minPopularity)/$range);
                $this->_tags[$tag->getName()] = $tag;
            }
            ksort($this->_tags);
        }
        return $this;
    }

    public function getTags()
    {
        $this->_loadTags();
        return $this->_tags;
    }

    public function getMaxPopularity()
    {
        return $this->_maxPopularity;
    }

    public function getMinPopularity()
    {
        return $this->_minPopularity;
    }

    public function toHtml()
    {
        if (count($this->getTags()) > 0) {
            return parent::toHtml();
        }
        return '';
    }
}