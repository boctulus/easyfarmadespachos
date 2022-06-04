<?php

/*
	@author boctulus
*/

namespace boctulus\EasyFarmaDespachos\libs;

use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Debug;

class Orders {
    static function getOrderById($order_id){
        // Get an instance of the WC_Order object (same as before)
        return wc_get_order($order_id);
    }

    static function getOrderItems(\Automattic\WooCommerce\Admin\Overrides\Order $order_object){
        return $order_object->get_items();
    }

    static function getOrderData(\Automattic\WooCommerce\Admin\Overrides\Order $order_object){
        $order_data = $order_object->get_data(); // The Order data

        $order_status   = $order_data['status'];
        $order_currency = $order_data['currency'];
        $order_payment_method = $order_data['payment_method'];
        $order_payment_method_title = $order_data['payment_method_title'];

        return [
            'status' => $order_status,
            'currency' => $order_currency,
            'payment_method' => $order_payment_method,
            'payment_method_title' => $order_payment_method_title
        ];
    }

    /*
        https://www.hardworkingnerd.com/woocommerce-how-to-get-a-customer-details-from-an-order/
    */
    static function getCustomerData(\Automattic\WooCommerce\Admin\Overrides\Order $order){
        // Get the customer or user id from the order object
        $customer_id = $order->get_customer_id();

        //this should return exactly the same number as the code above
        $user_id = $order->get_user_id();

        // Get the WP_User Object instance object
        $user = $order->get_user();


        /*
            Billing
        */
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name  = $order->get_billing_last_name();
        $billing_company    = $order->get_billing_company();
        $billing_address_1  = $order->get_billing_address_1();
        $billing_address_2  = $order->get_billing_address_2();
        $billing_city       = $order->get_billing_city();
        $billing_state      = $order->get_billing_state();
        $billing_postcode   = $order->get_billing_postcode();
        $billing_country    = $order->get_billing_country();

        //note that by default WooCommerce does not collect email and phone number for the shipping address
        //so these fields are only available on the billing address
        $billing_email  = $order->get_billing_email();
        $billing_phone  = $order->get_billing_phone();

        $billing_display_data = Array(
            "First Name" => $billing_first_name,
            "Last Name" => $billing_last_name,
            "Company" => $billing_company,
            "Address Line 1" => $billing_address_1,
            "Address Line 2" => $billing_address_2,
            "City" => $billing_city,
            "State" => $billing_state,
            "Post Code" => $billing_postcode,
            "Country" => $billing_country,
            "Email" => $billing_email,
            "Phone" => $billing_phone
        );

        /*
            Shipping
        */

        // Customer shipping information details
        $shipping_first_name = $order->get_shipping_first_name();
        $shipping_last_name  = $order->get_shipping_last_name();
        $shipping_company    = $order->get_shipping_company();
        $shipping_address_1  = $order->get_shipping_address_1();
        $shipping_address_2  = $order->get_shipping_address_2();
        $shipping_city       = $order->get_shipping_city();
        $shipping_state      = $order->get_shipping_state();
        $shipping_postcode   = $order->get_shipping_postcode();
        $shipping_country    = $order->get_shipping_country();

        $shipping_display_data = Array(
            "First Name" => $shipping_first_name,
            "Last Name" => $shipping_last_name,
            "Company" => $shipping_company,
            "Address Line 1" => $shipping_address_1,
            "Address Line 2" => $shipping_address_2,
            "City" => $shipping_city,
            "State" => $shipping_state,
            "Post Code" => $shipping_postcode,
            "Country" => $shipping_country,
            "Note" => $order->get_customer_note()
        );

        return [
            'customer_id' => $customer_id,
            'user_id'     => $user_id,
            'billing'  => $billing_display_data,
            'shipping' => $shipping_display_data
        ];

    }

    /*
        Recibe instancia de WC_Order_Item_Product y devuelve array
    */
    static function orderItemToArray($item) {
        //Get the product ID
        $product_id = $item->get_product_id();

        //Get the variation ID
        $variation_id = $item->get_variation_id();

        //Get the WC_Product object
        $product = $item->get_product();

        // The quantity
        $quantity = $item->get_quantity();

        // The product name
        $product_name = $item->get_name(); // … OR: $product->get_name();

        //Get the product SKU (using WC_Product method)
        $sku = $product->get_sku();

        // Get line item totals (non discounted)
        $total_non_discounted     = $item->get_subtotal(); // Total without tax (non discounted)
    
        // Get line item totals (discounted when a coupon is applied)
        $total_discounted     = $item->get_total(); // Total without tax (discounted)

        /*
            Impuestos
        */

        $total_tax_non_discounted = $item->get_subtotal_tax(); // Total tax (non discounted)
        $total_tax_discounted = $item->get_total_tax(); // Total tax (discounted)

        return [
            'product_id'   => $product_id,
            'product_type' => $variation_id > 0 ? 'variable' : 'simple',

            // $item['variation_id']
            'variation_id' => $variation_id ?? null,
            'qty'          => $quantity,
            'product_name' => $product_name,
            'sku'          => $sku,
            'weight'       => $product->get_weight(),
            'price'        => $product->get_regular_price(),   
            'sale_price'   => $product->get_sale_price(), 

            'total_non_discounted' => $total_non_discounted,
            'total_discounted' => $total_discounted,

            'total_tax_non_discounted'  => $total_tax_non_discounted,
            'total_tax_discounted' => $total_tax_discounted
        ];

    }

}