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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Adminhtml_Model_Search_Customer extends Varien_Object 
{
    public function load()
    {
        $arr = array();
        
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter(array(
                array('attribute'=>'firstname', 'like'=>$this->getQuery().'%'),
                array('attribute'=>'lastname', 'like'=>$this->getQuery().'%')
            ))
            ->setPage(1, 10)
            ->load();
        
        foreach ($collection->getItems() as $customer) {
            $arr[] = array(
                'id'            => 'customer/1/'.$customer->getId(),
                'type'          => 'Customer',
                'name'          => $customer->getFirstname().' '.$customer->getLastname(),
                'description'   => Mage::helper('adminhtml')->__('No description'),
                'url'           => Mage::getUrl('*/customer/edit', array('id'=>$customer->getId())),
            );
        }
        
        $this->setResults($arr);
        
        return $this;
    }
}
