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
 * Translation resource model
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Mysql4_Translate extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('core/translate', 'key_id');
    }

    public function getTranslationArray($storeId=null)
    {
        if(!Mage::app()->isInstalled()) {
            return array();
        }

        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $read = $this->_getReadAdapter();
        if (!$read) {
            return array();
        }

        $select = $read->select()
            ->from(array('main'=>$this->getMainTable()), array(
                    'string',
                    new Zend_Db_Expr('IFNULL(store.translate, main.translate)')
                ))
            ->joinLeft(array('store'=>$this->getMainTable()),
                $read->quoteInto('store.string=main.string AND store.store_id=?', $storeId),
                'string')
            ->where('main.store_id=0');
        return $read->fetchPairs($select);
    }
    
    public function getMainChecksum()
    {
        return parent::getChecksum($this->getMainTable());
    }
}
