<?php

/*
    Script destructivo

    Usar una sola vez *******
*/

/*
    Leer 

    https://code.tutsplus.com/tutorials/how-to-work-with-wordpress-post-metadata--cms-25715
*/

use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Arrays;
use boctulus\EasyFarmaDespachos\libs\Products;
// ...

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//echo "Current Memory: " .ini_get("memory_limit")."\r\n";
ini_set("memory_limit","728M");
//echo "Updates Memory: ".ini_get("memory_limit")."\r\n";


if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

	require_once ABSPATH . '/wp-config.php';
	require_once ABSPATH .'/wp-load.php';
}

require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Files.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Arrays.php';
require_once __DIR__ . '/libs/Products.php';

require_once __DIR__ . '/helpers/debug.php';


/*
    Usar con hook admin_init

    https://stackoverflow.com/a/28056864/980631
    https://www.danielcastanera.com/resetear-limpiar-los-loops-wordpress/ 
*/
function reset_prices(){
    /*
        7800063000770 =>
        array (
            'precio' => '2790',
            'precio_plus' => '1937',
        ),
        7804650880288 =>
        array (
            'precio' => '1390',
            'precio_plus' => '589',
        ),
    */
    
    $rows = include __DIR__ . '/completo-csv.php';

    $products = wc_get_products([
        //'status' => 'publish', 
        'limit' => -1
    ]);
    
    foreach ($products as $_p){
        $pid = $_p->get_id();
        $p = Products::getProduct($pid);
    
        $sku = $p->get_sku();

        dd([
            'pid' => $pid,
            'sku' => $sku
        ]);

        if ($sku == ''){
            continue;
        }
        
        // Anula el sale_price
        update_post_meta( $pid, '_sale_price', '' );
        
        // Lee el valor de regular_price
        $row = $rows[$sku];

        $regular_price  = $row['precio'];
        $precio_plus    = $row['precio_plus'];
    
        dd([
            [$regular_price, $precio_plus], 
            'PRECIOS'
        ]);

        update_post_meta( $pid, '_regular_price', $regular_price );

        // Setea el valor del regular price como (the) price
        update_post_meta( $pid, '_price', $regular_price );
    }
}

function run_once($fn, ...$args){
    $path = __DIR__ . '/runned.once';

    if (file_exists($path)){
        rename(__FILE__, __FILE__ . '.bk');
        exit;
    }

    $fn(...$args);

    file_put_contents($path, 'done');

    dd('FIN');
}

// run_once(
//     'reset_prices'
// );

reset_prices();