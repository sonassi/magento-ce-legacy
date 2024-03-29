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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        return array(
            'quote'=>array(
                'table'=>'sales/quote',
                'increment_model'=>'eav/entity_increment_alphanum',
                'increment_per_store'=>true,
                'attributes' => array(
                    'entity_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/quote_attribute_backend_parent'),
                    'is_active' => array('type'=>'static', 'visible'=>false),
                    'remote_ip' => array('visible'=>false),
                    'checkout_method' => array(),
                    'password_hash' => array(),
                    'quote_status_id' => array(
                        'label'=>'Quote Status',
                        'type'=>'int',
                        'source'=>'sales_entity/quote_attribute_source_status'),
                    'billing_address_id' => array('type'=>'int', 'visible'=>false),
                    'converted_at' => array('type'=>'datetime', 'visible'=>false),
                    'coupon_code' => array('label'=>'Coupon'),
                    'giftcert_code' => array('label'=>'Gift certificate'),
                    'custbalance_amount' => array('type'=>'decimal'),
                    'base_currency_code' => array('label'=>'Base currency'),
                    'store_currency_code' => array('label'=>'Store currency'),
                    'quote_currency_code' => array('label'=>'Quote currency'),
                    'store_to_base_rate' => array('type'=>'decimal', 'label'=>'Store to Base rate'),
                    'store_to_quote_rate' => array('type'=>'decimal', 'label'=>'Store to Quote rate'),
                    'grand_total' => array('type'=>'decimal'),
                    'orig_order_id' => array('label'=>'Original order ID'),
                    'applied_rule_ids' => array('type'=>'text', 'visible'=>false),
                    'is_virtual' => array('type'=>'int', 'visible'=>false),
                    'is_multi_shipping' => array('type'=>'int', 'visible'=>false),
                    'is_multi_payment' => array('type'=>'int', 'visible'=>false),
                    'customer_id' => array('type'=>'int', 'visible'=>false),
                    'customer_tax_class_id' => array('type'=>'int', 'visible'=>false),
                    'customer_group_id' => array('type'=>'int', 'visible'=>false),
                    'customer_email' => array('type'=>'varchar', 'visible'=>false),
                    'customer_firstname' => array('type'=>'varchar', 'visible'=>false),
                    'customer_lastname' => array('type'=>'varchar', 'visible'=>false),
                    'customer_note' => array('type'=>'text', 'visible'=>false),
                    'customer_note_notify' => array('type'=>'int', 'visible'=>false),
                ),
            ),
            'quote_address' => array(
                'table'=>'sales/quote',
                'attributes' => array(
                    'entity_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/quote_address_attribute_backend_parent',
                        'visible'=>false),
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/quote_attribute_backend_child',
                        'visible'=>false),
                    'address_type' => array('visible'=>false),
                    'customer_id' => array('type'=>'int', 'visible'=>false),
                    'customer_address_id' => array('type'=>'int', 'visible'=>false),
                    'email' => array('label'=>'Email', 'visible'=>false),
                    'firstname' => array('label'=>'First Name'),
                    'lastname' => array('label'=>'Last Name'),
                    'company' => array('label'=>'Company'),
                    'street' => array('label'=>'Street Address'),
                    'city' => array('label'=>'City'),
                    'region' => array('label'=>'State/Province'),
                    'region_id' => array('type'=>'int', 'visible'=>false),
                    'postcode' => array('label'=>'Zip/Postal Code'),
                    'country_id' => array('type'=>'varchar', 'visible'=>false),
                    'telephone' => array('label'=>'Telephone'),
                    'fax' => array('label'=>'Fax'),
                    'same_as_billing' => array('type'=>'int', 'label'=>'Same as billing', 'visible'=>false),
                    'weight' => array('type'=>'decimal', 'label'=>'Weight', 'visible'=>false),
                    'free_shipping' => array('type'=>'int'),
                    'collect_shipping_rates' => array('type'=>'int'),
                    'shipping_method' => array('label'=>'Shipping Method', 'visible'=>false),
                    'shipping_description' => array('type'=>'text', 'visible'=>false),
                    'subtotal' => array('type'=>'decimal', 'visible'=>false),
                    'subtotal_with_discount' => array('type'=>'decimal', 'visible'=>false),
                    'tax_amount' => array('type'=>'decimal', 'visible'=>false),
                    'shipping_amount' => array('type'=>'decimal', 'visible'=>false),
                    'discount_amount' => array('type'=>'decimal', 'visible'=>false),
                    'custbalance_amount' => array('type'=>'decimal', 'visible'=>false),
                    'grand_total' => array('type'=>'decimal', 'visible'=>false),
                    'customer_notes' => array('type'=>'text', 'label'=>'Customer Notes'),
                ),
            ),
            'quote_address_rate' => array(
                'table'=>'sales/quote_temp',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/quote_address_attribute_backend_child'),
                    'code' => array(),
                    'carrier' => array(),
                    'carrier_title' => array(),
                    'method' => array(),
                    'method_description' => array('type'=>'text'),
                    'price' => array('type'=>'decimal'),
                    'error_message' => array('type'=>'text'),
                ),
            ),
            'quote_address_item' => array(
                'table'=>'sales/quote_temp',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/quote_address_attribute_backend_child'),
                    'quote_item_id' => array('type'=>'int'),
                    'product_id' => array('type'=>'int'),
                    'super_product_id' => array('type'=>'int'),
                    'parent_product_id' => array('type'=>'int'),
                    'sku' => array(),
                    'image' => array(),
                    'name' => array(),
                    'description' => array('type'=>'text'),
                    'weight' => array('type'=>'decimal'),
                    'free_shipping' => array('type'=>'int'),
                    'qty' => array('type'=>'decimal'),
                    'price' => array('type'=>'decimal'),
                    'discount_percent' => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'no_discount' => array('type'=>'int'),
                    'tax_percent' => array('type'=>'decimal'),
                    'tax_amount' => array('type'=>'decimal'),
                    'row_total' => array('type'=>'decimal'),
                    'row_total_with_discount' => array('type'=>'decimal'),
                    'row_weight' => array('type'=>'decimal'),
                    'applied_rule_ids' => array(),
                ),
            ),
            'quote_item' => array(
                'table'=>'sales/quote',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/quote_attribute_backend_child'),
                    'product_id' => array('type'=>'int'),
                    'super_product_id' => array('type'=>'int'),
                    'parent_product_id' => array('type'=>'int'),
                    'sku' => array(),
                    'image' => array(),
                    'name' => array(),
                    'description' => array('type'=>'text'),
                    'weight' => array('type'=>'decimal'),
                    'free_shipping' => array('type'=>'int'),
                    'qty' => array('type'=>'decimal'),
                    'price' => array('type'=>'decimal'),
                    'discount_percent' => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'no_discount' => array('type'=>'int'),
                    'tax_percent' => array('type'=>'decimal'),
                    'tax_amount' => array('type'=>'decimal'),
                    'row_total' => array('type'=>'decimal'),
                    'row_total_with_discount' => array('type'=>'decimal'),
                    'row_weight' => array('type'=>'decimal'),
                    'applied_rule_ids' => array(),
                ),
            ),
            'quote_payment' => array(
                'table'=>'sales/quote',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/quote_attribute_backend_child'),
                    'customer_payment_id' => array('type'=>'int'),
                    'method' => array(),
                    'additional_data' => array('type'=>'text'),
                    'po_number' => array(),
                    'cc_type' => array(),
                    'cc_number_enc' => array(),
                    'cc_last4' => array(),
                    'cc_owner' => array(),
                    'cc_exp_month' => array('type'=>'int'),
                    'cc_exp_year' => array('type'=>'int'),
                    'cc_cid_enc' => array(),
                ),
            ),

            'order' => array(
                'table'=>'sales/order',
                'increment_model'=>'eav/entity_increment_numeric',
                'increment_per_store'=>true,
                'backend_prefix'=>'sales_entity/order_attribute_backend',
                'attributes' => array(
                    'entity_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_attribute_backend_parent'
                    ),

                    'remote_ip' => array(),

                    'status'    => array('type'=>'varchar'),
                    'state'     => array('type'=>'varchar'),
                    'is_hold'   => array('type'=>'int'),

                    'relation_parent_id'        => array('type'=>'varchar'),
                    'relation_parent_real_id'   => array('type'=>'varchar'),
                    'relation_child_id'         => array('type'=>'varchar'),
                    'relation_child_real_id'    => array('type'=>'varchar'),

                    'quote_id' => array('type'=>'int'),
                    'quote_address_id' => array('type'=>'int'),
                    'billing_address_id' => array('type'=>'int', 'backend'=>'_billing'),
                    'shipping_address_id' => array('type'=>'int', 'backend'=>'_shipping'),

                    'coupon_code'       => array(),
                    'applied_rule_ids'  => array(),
                    'giftcert_code'     => array(),

                    'base_currency_code'    => array(),
                    'store_currency_code'   => array(),
                    'order_currency_code'   => array(),
                    'store_to_base_rate'    => array('type'=>'decimal'),
                    'store_to_order_rate'   => array('type'=>'decimal'),

                    'is_virtual'        => array('type'=>'int'),
                    'is_multi_payment'  => array('type'=>'int'),

                    'shipping_method' => array(),
                    'shipping_description' => array(),
                    'weight' => array('type'=>'decimal'),

                    'tax_amount'        => array('type'=>'decimal'),
                    'shipping_amount'   => array('type'=>'decimal'),
                    'discount_amount'   => array('type'=>'decimal'),
                    'giftcert_amount'   => array('type'=>'decimal'),
                    'custbalance_amount'=> array('type'=>'decimal'),

                    'subtotal'      => array('type'=>'decimal'),
                    'grand_total'   => array('type'=>'decimal'),
                    'total_paid'    => array('type'=>'decimal'),
                    'total_due'     => array('type'=>'decimal'),
                    'total_refunded'=> array('type'=>'decimal'),
                    'total_qty_ordered' => array('type'=>'decimal'),

                    'customer_id'       => array('type'=>'int', 'visible'=>false),
                    'customer_group_id' => array('type'=>'int', 'visible'=>false),
                    'customer_email'    => array('type'=>'varchar', 'visible'=>false),
                    'customer_firstname'=> array('type'=>'varchar', 'visible'=>false),
                    'customer_lastname' => array('type'=>'varchar', 'visible'=>false),
                    'customer_note'     => array('type'=>'text', 'visible'=>false),
                    'customer_note_notify' => array('type'=>'int', 'visible'=>false),
                ),
            ),
            'order_address' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/order_attribute_backend_child'),
                    'quote_address_id' => array('type'=>'int'),
                    'address_type' => array(),
                    'customer_id' => array('type'=>'int'),
                    'customer_address_id' => array('type'=>'int'),
                    'email' => array(),
                    'firstname' => array(),
                    'lastname'  => array(),
                    'company'   => array(),
                    'street'    => array(),
                    'city'      => array(),
                    'region'    => array(),
                    'region_id' => array('type'=>'int'),
                    'postcode'  => array(),
                    'country_id'=> array('type'=>'varchar'),
                    'telephone' => array(),
                    'fax'       => array(),

                ),
            ),
            'order_item' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_attribute_backend_child'
                    ),

                    'quote_item_id'     => array('type'=>'int'),
                    'product_id'        => array('type'=>'int'),
                    'super_product_id'  => array('type'=>'int'),
                    'parent_product_id' => array('type'=>'int'),
                    'sku'               => array(),
                    'name'              => array(),
                    'description'       => array('type'=>'text'),
                    'weight'            => array('type'=>'decimal'),

                    'qty_ordered'       => array('type'=>'decimal'),
                    'qty_backordered'   => array('type'=>'decimal'),
                    'qty_invoiced'      => array('type'=>'decimal'),
                    'qty_canceled'      => array('type'=>'decimal'),
                    'qty_shipped'       => array('type'=>'decimal'),
                    'qty_refunded'      => array('type'=>'decimal'),

                    'original_price'    => array('type'=>'decimal'),
                    'price'             => array('type'=>'decimal'),
                    'cost'              => array('type'=>'decimal'),

                    'discount_percent'  => array('type'=>'decimal'),
                    'discount_amount'   => array('type'=>'decimal'),
                    'discount_invoiced' => array('type'=>'decimal'),

                    'tax_percent'       => array('type'=>'decimal'),
                    'tax_amount'        => array('type'=>'decimal'),
                    'tax_invoiced'      => array('type'=>'decimal'),

                    'row_total'         => array('type'=>'decimal'),
                    'row_weight'        => array('type'=>'decimal'),
                    'row_invoiced'      => array('type'=>'decimal'),
                    'applied_rule_ids'  => array(),

                    'invoiced_total'   => array('type'=>'decimal'),
                    'amount_refunded'   => array('type'=>'decimal'),
                ),
            ),
            'order_payment' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_attribute_backend_child'
                    ),
                    'quote_payment_id'      => array('type'=>'int'),
                    'customer_payment_id'   => array('type'=>'int'),
                    'method'                => array(),
                    'additional_data'       => array('type'=>'text'),

                    'po_number'     => array(),

                    'cc_type'       => array(),
                    'cc_number_enc' => array(),
                    'cc_last4'      => array(),
                    'cc_owner'      => array(),
                    'cc_exp_month'  => array(),
                    'cc_exp_year'   => array(),

                    'cc_status'             => array(),
                    'cc_status_description' => array(),
                    'cc_trans_id'           => array(),
                    'cc_approval'           => array(),
                    'cc_avs_status'         => array(),
                    'cc_cid_status'         => array(),

                    'cc_debug_request_body' => array(),
                    'cc_debug_response_body'=> array(),
                    'cc_debug_response_serialized' => array(),

                    'anet_trans_method'     => array(),
                    'echeck_routing_number' => array(),
                    'echeck_bank_name'      => array(),
                    'echeck_account_type'   => array(),
                    'echeck_account_name'   => array(),
                    'echeck_type'           => array(),

                    'amount_ordered'    => array('type'=>'decimal'),
                    'amount_authorized' => array('type'=>'decimal'),
                    'amount_paid'       => array('type'=>'decimal'),
                    'amount_canceled'   => array('type'=>'decimal'),
                    'amount_refunded'   => array('type'=>'decimal'),
                    'shipping_amount'   => array('type'=>'decimal'),
                    'shipping_captured' => array('type'=>'decimal'),
                    'shipping_refunded' => array('type'=>'decimal'),
                ),
            ),

            'order_status_history' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_attribute_backend_child'
                    ),
                    'status'    => array('type'=>'varchar'),
                    'comment'   => array('type'=>'text'),
                    'is_customer_notified' => array('type'=>'int'),
                ),
            ),

            'invoice' => array(
                //'table'=>'sales/invoice',
                'table'=>'sales/order',
                'increment_model'=>'eav/entity_increment_numeric',
                'increment_per_store'=>true,
                'backend_prefix'=>'sales_entity/order_attribute_backend',
                'attributes' => array(
                    'entity_id'     => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_invoice_attribute_backend_parent'
                    ),
                    'state'    => array('type'=>'int'),
                    'transaction_id' => array(),


                    'order_id'              => array(
                        'type'=>'int',
                        'backend'=>'sales_entity/order_invoice_attribute_backend_order'
                    ),

                    'billing_address_id'    => array('type'=>'int'),
                    'shipping_address_id'   => array('type'=>'int'),

                    'base_currency_code'    => array(),
                    'store_currency_code'   => array(),
                    'order_currency_code'   => array(),
                    'store_to_base_rate'    => array('type'=>'decimal'),
                    'store_to_order_rate'   => array('type'=>'decimal'),

                    'subtotal'          => array('type'=>'decimal'),
                    'discount_amount'   => array('type'=>'decimal'),
                    'tax_amount'        => array('type'=>'decimal'),
                    'shipping_amount'   => array('type'=>'decimal'),
                    'grand_total'       => array('type'=>'decimal'),
                    'total_qty'         => array('type'=>'decimal'),

                    'can_void_flag'     => array('type'=>'int'),
                ),
            ),

            'invoice_item' => array(
                //'table'=>'sales/invoice',
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id'     => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_invoice_attribute_backend_child'
                    ),
                    'order_item_id' => array('type'=>'int'),
                    'product_id'    => array('type'=>'int'),
                    'name'          => array(),
                    'description'   => array('type'=>'text'),
                    'sku'           => array(),
                    'qty'           => array('type'=>'decimal'),
                    'price'         => array('type'=>'decimal'),
                    'cost'          => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'tax_amount'    => array('type'=>'decimal'),
                    'row_total'     => array('type'=>'decimal'),
                ),
            ),

            'invoice_comment' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_invoice_attribute_backend_child'
                    ),
                    'comment' => array('type'=>'text'),
                    'is_customer_notified' => array('type'=>'int'),
                ),
            ),



            'shipment' => array(
                //'table'=>'sales/shipment',
                'table'=>'sales/order',
                'increment_model'=>'eav/entity_increment_numeric',
                'increment_per_store'=>true,
                'backend_prefix'=>'sales_entity/order_attribute_backend',
                'attributes' => array(
                    'entity_id'     => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_shipment_attribute_backend_parent'
                    ),

                    'customer_id'   => array('type'=>'int'),
                    'order_id'      => array('type'=>'int'),
                    'shipment_status'     => array('type'=>'int'),
                    'billing_address_id'    => array('type'=>'int'),
                    'shipping_address_id'   => array('type'=>'int'),

                    'total_qty'         => array('type'=>'decimal'),
                    'total_weight'      => array('type'=>'decimal'),
                ),
            ),

            'shipment_item' => array(
                //'table'=>'sales/shipment',
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id'     => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_shipment_attribute_backend_child'
                    ),
                    'order_item_id' => array('type'=>'int'),
                    'product_id'    => array('type'=>'int'),
                    'name'          => array(),
                    'description'   => array('type'=>'text'),
                    'sku'           => array(),
                    'qty'           => array('type'=>'decimal'),
                    'price'         => array('type'=>'decimal'),
                    'weight'        => array('type'=>'decimal'),
                    'row_total'     => array('type'=>'decimal'),
                ),
            ),

            'shipment_comment' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_shipment_attribute_backend_child'
                    ),
                    'comment' => array('type'=>'text'),
                    'is_customer_notified' => array('type'=>'int'),
                ),
            ),

            'shipment_track' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id'     => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_shipment_attribute_backend_child'
                    ),
                    'order_id'      => array('type'=>'int'),
                    'number'        => array('type'=>'text'),
                    'carrier_code'  => array('type'=>'varchar'),
                    'title'         => array('type'=>'varchar'),
                    'description'   => array('type'=>'text'),
                    'qty'           => array('type'=>'decimal'),
                    'weight'        => array('type'=>'decimal'),
                ),
            ),

            'creditmemo' => array(
                //'table'=>'sales/creditmemo',
                'table'=>'sales/order',
                'increment_model'=>'eav/entity_increment_numeric',
                'increment_per_store'=>true,
                'backend_prefix'=>'sales_entity/order_attribute_backend',
                'attributes' => array(
                    'entity_id'     => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_creditmemo_attribute_backend_parent'
                    ),
                    'state'         => array('type'=>'int'),
                    'transaction_id'=> array(),

                    'order_id'      => array('type'=>'int'),
                    'creditmemo_status'     => array('type'=>'int'),
                    'billing_address_id'    => array('type'=>'int'),
                    'shipping_address_id'   => array('type'=>'int'),

                    'base_currency_code'    => array(),
                    'store_currency_code'   => array(),
                    'order_currency_code'   => array(),
                    'store_to_base_rate'    => array('type'=>'decimal'),
                    'store_to_order_rate'   => array('type'=>'decimal'),

                    'subtotal'          => array('type'=>'decimal'),
                    'discount_amount'   => array('type'=>'decimal'),
                    'tax_amount'        => array('type'=>'decimal'),
                    'shipping_amount'   => array('type'=>'decimal'),
                    'adjustment'        => array('type'=>'decimal'),
                    'adjustment_positive' => array('type'=>'decimal'),
                    'adjustment_negative' => array('type'=>'decimal'),
                    'grand_total'       => array('type'=>'decimal'),
                ),
            ),

            'creditmemo_item' => array(
                //'table'=>'sales/creditmemo',
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id'     => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_creditmemo_attribute_backend_child'
                    ),
                    'order_item_id' => array('type'=>'int'),
                    'product_id'    => array('type'=>'int'),
                    'name'          => array(),
                    'description'   => array('type'=>'text'),
                    'sku'           => array(),
                    'qty'           => array('type'=>'decimal'),
                    'price'         => array('type'=>'decimal'),
                    'cost'          => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'tax_amount'    => array('type'=>'decimal'),
                    'row_total'     => array('type'=>'decimal'),
                ),
            ),

            'creditmemo_comment' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array(
                        'type'=>'static',
                        'backend'=>'sales_entity/order_creditmemo_attribute_backend_child'
                    ),
                    'comment' => array('type'=>'text'),
                    'is_customer_notified' => array('type'=>'int'),
                ),
            ),

        );
    }
}
