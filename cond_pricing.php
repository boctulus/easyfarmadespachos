<?php

use boctulus\EasyFarmaDespachos\libs\Url;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Users;
use boctulus\EasyFarmaDespachos\libs\Products;
use boctulus\EasyFarmaDespachos\libs\EasyFarma; 

require_once __DIR__ . '/libs/Url.php';
require_once __DIR__ . '/libs/Files.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Users.php';
require_once __DIR__ . '/libs/Products.php';
require_once __DIR__ . '/libs/EasyFarma.php';


add_action( 'woocommerce_single_product_summary', 'product_page_with_radios', 15 );


function product_page_with_radios() {
    $config = include __DIR__ . '/config/config.php';

    if (!is_user_logged_in() || !Users::hasRole($config['vip_membership_user'], get_current_user_id())){
        return;
    }

    ?>

    <script>
        const SITE_URL = '<?= get_site_url(); ?>';
		
        function set_as_plus(){
            if (jQuery('input[name="price_type"]').length == 0){
                jQuery(jQuery("form.cart")[0]).append('<input type="hidden" name="price_type" value="plus">');
            } else {
                jQuery('input[name="price_type"]').val('plus')
            }
        }

        function set_as_normal(){
            if (jQuery('input[name="price_type"]').length == 0){
                jQuery(jQuery("form.cart")[0]).append('<input type="hidden" name="price_type" value="normal">');
            } else {
                jQuery('input[name="price_type"]').val('normal')
            }
        }

        jQuery(jQuery('.price > .woocommerce-Price-amount > bdi')[0]).replaceWith('<input type="radio" id="precio_plus" name="radio_price_type" value="plus">	<label for="precio_plus">EasyFarma Plus</label><br>	<input type="radio" id="precio_normal" name="radio_price_type" value="normal"> <label for="precio_normal">Precio normal</label><br>')


        jQuery('input:radio[name="radio_price_type"]').change(
        function(){
            if (this.checked) {
                if (this.value == 'normal'){
                    set_as_normal()
                } else {
                    set_as_plus()
                }
            } 
        });

	</script>

    <?php
};     

/*
    HOOK para precios condicionales
*/


add_action('woocommerce_add_to_cart', 'custome_add_to_cart');

function custome_add_to_cart() 
{
    //check if product already in cart
    if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
        foreach ( WC()->cart->get_cart() as $cart_item_key => $data ) 
        {
            $product_id   = $data['product_id'];
            $variation_id = $data['variation_id'];
            $quantity     = $data['quantity'];
            $variation    = $data['variation'];
            $sku          = Products::getSKUFromProductId($product_id);
        
            // Cantidad maxima que debe depender de cierta logica pero donde 2 es el maximo absoluto
            $max = 2; 

            if ($quantity > $max){
                // Split

                $extra_qty = $quantity -$max;

                $plus_prod_id = Products::getProductIdBySKU("{$sku}_2");
                
                // Remuevo producto original
                WC()->cart->remove_cart_item( $cart_item_key );

                // Agrego productos
                WC()->cart->add_to_cart( $product_id, $max, $variation_id, $variation);
                WC()->cart->add_to_cart( $plus_prod_id, $extra_qty, $variation_id, $variation);
            }

        //    dd(
        //         [
        //             $data['product_id'],
        //             $data['variation_id'],
        //             $data['quantity'],
        //             $data['variation']
        //         ]
        //    );
            
        }
    }

    // = 
}



//add_filter('woocommerce_product_get_price', 'custom_price_easyfarma_plus_role', 10, 2);

/**
 * custom_price_easyfarma_plus_role 
 *
 * filter the price based on category and user role
 * @param  $price   
 * @param  $product 
 * @return
 * 
 * Notas: solo funciona para productos simples
 * 
 * https://wordpress.stackexchange.com/a/111788/99153 
 */

 /*
    Se ejecuta en 
    
    /cart/
    /cart/{producto}


    Restricción:

    límite máximo de 2 medicamentos easyfarma plus al mes por client
 */
 function custom_price_easyfarma_plus_role($price, $product) {
    $config = include __DIR__ . '/config/config.php';

    /*
        En la pàgina de producto se envia por POST

        $_POST = 
        array (
            'quantity' => '1',
            'add-to-cart' => '4173',
            'price_type' => 'Plus',
        )

        En cambio, en ¨archives¨ se envia por GET

        array (
           'add-to-cart' => '7397',
        )

        Ej:

        http://easyfarma.lan/tienda/nexium-40-mg-x-28-comprimidos/?add-to-cart=108

        y tocaria hacer el append del price_type

        http://easyfarma.lan/tienda/nexium-40-mg-x-28-comprimidos/?add-to-cart=108&price_type=plus
    */

    if (!is_user_logged_in() || !isset($_REQUEST['price_type'])){
        return $price;
    } 
    
    Files::localDump($_REQUEST, 'REQUEST.txt');

    if ($_REQUEST['price_type'] == 'normal'){
        return $price;
    }

    // El precio podria ser 'normal' o 'plus'

    $prod_id = $product->get_id();
    $user_id = get_current_user_id();

    if (Users::hasRole($config['vip_membership_user'], $user_id)){
        $price_plus = EasyFarma::getPrecioPlus($prod_id);

        if (!empty($price_plus)){
            $price = $price_plus;
        }
    }
    
    return $price;
}
