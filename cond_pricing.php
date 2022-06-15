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
        $_POST = 
        array (
        'quantity' => '1',
        'add-to-cart' => '4173',
        'price_type' => 'Plus',
        )
    */
    
    if (!is_user_logged_in() || !isset($_POST['price_type'])){
        return $price;
    } 

    if ($_POST['price_type'] == 'normal'){
        return $price;
    }

    // Si es un precio plus el elegido .... deberia crear el producto dinamicamente

    $prod_id = $product->get_id();
    $user_id = get_current_user_id();

    if (Users::hasRole('easyfarma_vip', $user_id)){
        $price_plus = EasyFarma::get_precio_plus($prod_id);

        if (!empty($price_plus)){
            $price = $price_plus;
        }
    }
    
    return $price;
}
