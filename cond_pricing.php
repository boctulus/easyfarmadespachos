<?php

use boctulus\EasyFarmaDespachos\libs\Url;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Users;
use boctulus\EasyFarmaDespachos\libs\EasyFarma; 

require_once __DIR__ . '/libs/Url.php';
require_once __DIR__ . '/libs/Files.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Users.php';
require_once __DIR__ . '/libs/EasyFarma.php';


if (Strings::startsWith('/tienda/', $_SERVER['REQUEST_URI']) && !Strings::endsWith('/tienda/', $_SERVER['REQUEST_URI'])):
	?>
	<script>
        const SITE_URL = '<?= get_site_url(); ?>';

        jQuery(jQuery('.price > .woocommerce-Price-amount > bdi')[0]).replaceWith('<input type="radio" id="precio_plus" name="price_type" value="plus">	<label for="precio_plus">EasyFarma Plus</label><br>	<input type="radio" id="precio_normal" name="price_type" value="normal"> <label for="precio_normal">Precio normal</label><br>')
		
        function set_as_plus(){
            jQuery("form.cart").append('<input type="hidden" name="price_type" value="Plus">');
        }

	</script>
<?php
endif;

/*
    HOOK para precios condicionales
*/

add_filter('woocommerce_product_get_price', 'custom_price_easyfarma_plus_role', 10, 2);

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

        http://easyfarma.lan/tienda/nexium-40-mg-x-28-comprimidos/?add-to-cart=108&price_type=Plus


        Aca:

        <div class="product-action-wrap">
        
        <a href="?add-to-cart=7396" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="7396" data-product_sku="4001638097048" aria-label="Añade “Aceite Corporal Refrescante Citrus 100ml Weleda” a tu carrito" rel="nofollow">Agregar <span class="kadence-svg-iconset svg-baseline">
        
        <svg class="kadence-svg-icon kadence-spinner-svg" fill="currentColor" version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" data-darkreader-inline-fill=""></path>
				</svg></span> .... </a>
        </div>

    */

    // dd($_REQUEST, '_REQUEST');
    // die; ///
    
    if (!is_user_logged_in() || !isset($_REQUEST['price_type'])){
        return $price;
    } 
    
    Files::localDump($_REQUEST, 'REQUEST.txt');

    if ($_REQUEST['price_type'] == 'normal'){
        return $price;
    }

    // Si es un precio plus el elegido .... deberia crear el producto dinamicamente

    $prod_id = $product->get_id();
    $user_id = get_current_user_id();

    if (Users::hasRole('easyfarma_vip', $user_id)){
        $price_plus = EasyFarma::getPrecioPlus($prod_id);

        if (!empty($price_plus)){
            $price = $price_plus;
        }
    }
    
    return $price;
}
