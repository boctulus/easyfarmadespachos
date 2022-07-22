<?php

use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Products;
use boctulus\EasyFarmaDespachos\libs\Users; ///
use boctulus\EasyFarmaDespachos\libs\Orders; ///
use boctulus\EasyFarmaDespachos\libs\EasyFarma; ///
// ...


if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

	require_once ABSPATH . '/wp-config.php';
	require_once ABSPATH .'/wp-load.php';
}

require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Products.php';
require_once __DIR__ . '/libs/Users.php';
require_once __DIR__ . '/libs/Orders.php';
require_once __DIR__ . '/libs/EasyFarma.php'; ///

ini_set("memory_limit","4096M");


if (!function_exists('dd')){
	function dd($val, $msg = null, $pre_cond = null){
		Debug::dd($val, $msg, $pre_cond);
	}
}


global $wpdb;

$cli = (php_sapi_name() == 'cli');


if (!$cli){
	echo "Ejecutar desde la terminal";
}

$ids = Products::getIDs();

foreach ($ids as $id){
    $p    = Products::getProduct($id);
    $sku  = $p->get_sku();
	// $name = $p->get_name();
	// $precio_plus = Products::getMeta($id, 'precio_plus');
    // $precio_reg  = $p->get_regular_price();  
    // $precio      = $p->get_price();

    if (Strings::endsWith('_2', $sku)){
		dd("Ocultando producto con SKU $sku | PID = $id");
		Products::hide($id);
		//$p->save();
    }
}


exit;
//////////

// Clean up

$ids = Products::getIDs();

dd(count($ids));

foreach ($ids as $id){
    $p    = Products::getProduct($id);
    $sku  = $p->get_sku();
	$name = $p->get_name();
	$precio_plus = Products::getMeta($id, 'precio_plus');
    $precio_reg  = $p->get_regular_price();  
    $precio      = $p->get_price();

    //if (Strings::endsWith('_2', $sku)){

		/*
			No puede haber producto "gemelo" Plus sin precio
		*/
		if (empty($precio_plus)){
			if (!empty($precio_reg)){
				$precio_plus = $precio_reg;
			} else {
				$precio_plus = $precio;
			}

			dd("Reparando precio de prod. con PID = $id | SKU = $sku");
			Products::updatePrice($id, $precio_plus);
			///Products::setMeta($id, 'precio_plus', $precio_plus);
		}

    //}
}


// foreach ($ids as $id){
//     $p    = Products::getProduct($id);
//     $sku  = $p->get_sku();
// 	$name = $p->get_name();

//     if ($sku == '_2' || Strings::endsWith('_2_2', $sku) || Strings::endsWith("EasyFarma Plus | EasyFarma Plus", $name)){
//         dd("Borrar $sku");
//         Products::deleteProduct($id, true);
//     }
// }


exit;

