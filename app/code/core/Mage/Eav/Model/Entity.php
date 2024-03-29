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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Eav_Model_Entity extends Mage_Eav_Model_Entity_Abstract
{
    const DEFAULT_ENTITY_MODEL = 'eav/entity';
    const DEFAULT_ATTRIBUTE_MODEL = 'eav/entity_attribute';
    const DEFAULT_BACKEND_MODEL = 'eav/entity_attribute_backend_default';
    const DEFAULT_FRONTEND_MODEL = 'eav/entity_attribute_frontend_default';
    const DEFAULT_SOURCE_MODEL = 'eav/entity_attribute_source_config';
    
    const DEFAULT_ENTITY_TABLE = 'eav/entity';
    const DEFAULT_ENTITY_ID_FIELD = 'entity_id';
    const DEFAULT_VALUE_TABLE_PREFIX = 'eav/entity_attribute';
    
    public function __construct()
    {
        $this->setConnection(Mage::getSingleton('core/resource')->getConnection('core_read'));
    }

}