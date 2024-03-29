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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category url key attribute backend
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Category_Attribute_Backend_Urlkey extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    public function beforeSave($object)
    {
    	$attributeName = $this->getAttribute()->getName();

    	$urlKey = $object->getData($attributeName);
    	if ($urlKey=='') {
    		$urlKey = $object->getName();
    	}

		$object->setData($attributeName, $object->formatUrlKey($urlKey));

		return $this;
    }

    public function afterSave($object)
    {
        if ($object->dataHasChangedFor('parent_id') || $object->dataHasChangedFor('url_key')) {
            if (! $object->getInitialSetupFlag() && ! $object->getNotUpdateDepends()) {
                Mage::getSingleton('catalog/url')->refreshRewrites(null, $object->getId());
            }
        }
    }

}
