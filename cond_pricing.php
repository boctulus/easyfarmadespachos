<?php

use boctulus\EasyFarmaDespachos\libs\Url;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Users;
use boctulus\EasyFarmaDespachos\libs\Carrito; 
use boctulus\EasyFarmaDespachos\libs\Products;
use boctulus\EasyFarmaDespachos\libs\EasyFarma; 

require_once __DIR__ . '/libs/Url.php';
require_once __DIR__ . '/libs/Files.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Users.php';
require_once __DIR__ . '/libs/Carrito.php';
require_once __DIR__ . '/libs/Products.php';
require_once __DIR__ . '/libs/EasyFarma.php';


/*
    Me saco de encima el enlace a "Ver carrito" porque permite saltarse restricciones

    Dejo de funcionar
*/
add_action( 'init', function(){
    if (!is_admin()){
        ?>
        <script>
            function removeVerCarrito(){
                let index = <?= wp_is_mobile() ? 1 : 0 ?>

                console.log(index)
                jQuery(jQuery(jQuery('p.woocommerce-mini-cart__buttons')[index]).children('a.wc-forward')[0]).remove();
                
            }
    
            document.addEventListener("DOMContentLoaded", function(event) { 
                let link_removed = false;
                
                // setTimeout(function(){
                //     removeVerCarrito();
                // }, 1000);

                setTimeout(function(){
                    if (link_removed){
                        return;
                    }

                    jQuery('.product-quantity > .quantity > input').prop("disabled", true);

                    removeVerCarrito();
                    link_removed = true;
                }, 2000)

               
            });

        </script>
        <?php
    }
});


add_action( 'woocommerce_single_product_summary', 'product_page_two_prices', 15 );


function product_page_two_prices() {
    $es_vip = EasyFarma::esVIP();

    if (!config()['show_easyfarma_plus_to_non_members']){
        if (!$es_vip){
            return;
        }
    }

    global $product;
    $pid = $product->get_id();

    $p   = Products::getProduct($pid);

    $precio_plus = Products::getMeta($pid, 'precio_plus');
    $precio      = $p->get_price();

    $precio_plus = Strings::formatNumber($precio_plus);
    $precio      = Strings::formatNumber($precio);

    ?>

    <script>
        const SITE_URL = '<?= get_site_url(); ?>';  

        let precio      = '<?= $precio ?>'
        let precio_plus = '<?= $precio_plus ?>'

        const es_vip    = <?= $es_vip ? 'true' : 'false' ?>

        let line_plus   = `<span class="ef_plus_price">EasyFarma Plus $ ${precio_plus}</span>`
        let line_normal = `<span class="ef_normal_price">Normal $ ${precio }</span>`

        if (!es_vip){
            line_plus   = `<del>${line_plus}</del>`
        }

        let reemplazo = `${line_plus}<br/>${line_normal}`

        jQuery(jQuery('.price > .woocommerce-Price-amount > bdi')[0]).replaceWith(reemplazo)

	</script>

    <?php
};     

/*
    HOOK para precios condicionales
*/

add_action('woocommerce_add_to_cart', 'add_custom_logic_to_cart');

function add_custom_logic_to_cart() 
{
    $config = include __DIR__ . '/config/config.php';

    /*
        Sino esta logueado no tiene sentido seguir
    */
    if (!EasyFarma::esVIP()){
        return;
    }


    $max_abs_plus = $config['max_per_user_and_month'];
    
    //check if product already in cart
    if ( sizeof( WC()->cart->get_cart() ) == 0 ) {
        return;
    }
        
    $items = WC()->cart->get_cart();
    $last  = end($items); // ultimo item agregado al carrito (en principio no se si es Plus o no)

    $product_id   = $last['product_id'];
    $variation_id = $last['variation_id'];
    $quantity     = $last['quantity'];
    $variation    = $last['variation'];
    $sku          = Products::getSKUFromProductId($product_id);

    $cant_en_carrito_plus   = 0; 
    $cant_en_carrito_normal = 0; 
    
    $prod_es_plus           = false;
    $hay_gemelo_en_carrito  = false;

    // Si el producto es con precio Plus
    if (Strings::endsWith('_2', $sku)){
        $prod_id_plus   = $product_id;
        $cant_en_carrito_plus = $quantity;

        $_sku_normal    = Strings::before($sku, '_2');
        $prod_id_normal = Products::getProductIdBySKU($_sku_normal);

        $prod_es_plus = true;
    } else {
        // Si el producto viene con precio normal

        $cant_en_carrito_normal = $quantity;
        $prod_id_plus   = Products::getProductIdBySKU("{$sku}_2");
        $prod_id_normal = $product_id;
    }   

    $cant_compras_mensuales_plus = EasyFarma::getBuyedQuantityEasyFarmaPlusPerUser($prod_id_plus);
    
    if ($prod_es_plus){
        $cart_item_key = Carrito::find($prod_id_normal);

        $cart_items = WC()->cart->get_cart_contents($cart_item_key);

        if (isset($cart_items[$cart_item_key])){
            $gemelo = $cart_items[$cart_item_key];
            $cant_en_carrito_normal = $gemelo['quantity'];

            $hay_gemelo_en_carrito = true;
        }
    } else {
        $cart_item_key = Carrito::find($prod_id_plus);

        $cart_items = WC()->cart->get_cart_contents($cart_item_key);

        if (isset($cart_items[$cart_item_key])){
            $gemelo = $cart_items[$cart_item_key];
            $cant_en_carrito_plus = $gemelo['quantity'];

            $hay_gemelo_en_carrito = true;
        }
    }
    
    // Debug
    // Files::localDump([
    //     'cant_normal' => $cant_en_carrito_normal,
    //     'cant_plus'   => $cant_en_carrito_plus,
    //     'cant_compras_mensuales_plus' => $cant_compras_mensuales_plus,
    //     'max_abs'     => $max_abs_plus

    // ], 'CARRITO.txt', true);

    /*
        Aplico la logica que ... debe reflejarse en operaciones sobre el carrito
    */

    if (!Users::hasRole($config['vip_membership_user'], get_current_user_id())){
        $cant_en_carrito_normal += $cant_en_carrito_plus;
        $cant_en_carrito_plus   = 0;
    } else {
        EasyFarma::cartLogic($cant_en_carrito_plus, $cant_en_carrito_normal, $cant_compras_mensuales_plus, $max_abs_plus);
    }
    
    // Debug
    // Files::localDump([
    //     'cant_normal (luego de ajuste)' => $cant_en_carrito_normal,
    //     'cant_plus  (luego de ajuste)'   => $cant_en_carrito_plus,
    //     'cant_compras_mensuales_plus' => $cant_compras_mensuales_plus,
    //     'max_abs'     => $max_abs_plus

    // ], 'CARRITO.txt', true);

    /*
        Aplico operaciones sobre el carrito
    */

    if ($prod_es_plus){
        Carrito::setQuantity($product_id, $cant_en_carrito_plus);

        if (!$hay_gemelo_en_carrito){
            Carrito::addToCart($prod_id_normal, $cant_en_carrito_normal);
        } else {
            Carrito::setQuantity($prod_id_normal, $cant_en_carrito_normal);
        }

    } else {
        // Es normal
        Carrito::setQuantity($product_id, $cant_en_carrito_normal);

        if (!$hay_gemelo_en_carrito){
            Carrito::addToCart($prod_id_plus, $cant_en_carrito_plus);
        } else {
            Carrito::setQuantity($prod_id_plus, $cant_en_carrito_plus);
        }
    }
 
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

 */
//  function custom_price_easyfarma_plus_role($price, $product) {
//     $config = include __DIR__ . '/config/config.php';

//     if (!is_user_logged_in() || !isset($_REQUEST['price_type'])){
//         return $price;
//     } 
    
//     Files::localDump($_REQUEST, 'REQUEST.txt');

//     if ($_REQUEST['price_type'] == 'normal'){
//         return $price;
//     }

//     // El precio podria ser 'normal' o 'plus'

//     $prod_id = $product->get_id();
//     $user_id = get_current_user_id();

//     if (Users::hasRole($config['vip_membership_user'], $user_id)){
//         $price_plus = EasyFarma::getPrecioPlus($prod_id);

//         if (!empty($price_plus)){
//             $price = $price_plus;
//         }
//     }
    
//     return $price;
// }
