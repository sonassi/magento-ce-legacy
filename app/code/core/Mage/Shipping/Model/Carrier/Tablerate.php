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
 * @package    Mage_Shipping
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Shipping_Model_Carrier_Tablerate extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_conditionNames = array();

    public function __construct()
    {
        foreach ($this->getCode('condition_name') as $k=>$v) {
            $this->_conditionNames[] = $k;
        }
    }

	/**
	 * Enter description here...
	 *
	 * @param Mage_Shipping_Model_Rate_Request $data
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::getStoreConfig('carriers/tablerate/active')) {
            return false;
        }

        if (!$request->getConditionName()) {
            $request->setConditionName(Mage::getStoreConfig('carriers/tablerate/condition_name'));
        }

        $result = Mage::getModel('shipping/rate_result');
        $rate = $this->getRate($request);
        if (!empty($rate) && $rate['price'] >= 0) {
	    	$method = Mage::getModel('shipping/rate_result_method');

	    	$method->setCarrier('tablerate');
	    	$method->setCarrierTitle(Mage::getStoreConfig('carriers/tablerate/title'));

	    	$method->setMethod('bestway');
	    	$method->setMethodTitle(Mage::getStoreConfig('carriers/tablerate/name'));

	    	$method->setPrice($rate['price']);
	    	$method->setCost($rate['cost']);

    	    $result->append($method);
        }

    	return $result;
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        return Mage::getResourceModel('shipping/carrier_tablerate')->getRate($request);
    }

    public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
            ),

            'condition_name_short'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight (and above)'),
                'package_value'  => Mage::helper('shipping')->__('Order Subtotal (and above)'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items (and above)'),
            ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];                                
    }
}
