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

 function custom_price_easyfarma_plus_role($price, $product) {
    if (!is_user_logged_in()) return $price;

    $pid = $product->get_id();
  
    if (Users::hasRole('easyfarma_vip', $pid)){
        $price_plus = EasyFarma::get_precio_plus($pid);

        if (!empty($price_plus)){
            $price = $price_plus;
        }
    }
    
    return $price;
}

/**
 * has_role_WPA111772 
 *
 * function to check if a user has a specific role
 * 
 * @param  string  $role    role to check against 
 * @param  int  $user_id    user id
 * @return boolean
 */
function has_role_WPA111772($role = '',$user_id = null){
    if ( is_numeric( $user_id ) )
        $user = get_user_by( 'id',$user_id );
    else
        $user = wp_get_current_user();

    if ( empty( $user ) )
        return false;

    return in_array( $role, (array) $user->roles );
}