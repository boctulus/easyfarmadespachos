<?php

use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Orders;
use boctulus\EasyFarmaDespachos\libs\Products;

// ...


if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

	require_once ABSPATH . '/wp-config.php';
	require_once ABSPATH .'/wp-load.php';
}

require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Orders.php';
require_once __DIR__ . '/libs/Products.php';

require_once __DIR__ . '/helpers/cli.php';

/*
	Mostrar todos los errores
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


global $wpdb;

$cli = (php_sapi_name() == 'cli');


if (!$cli){
	echo "Ejecutar desde la terminal";
}

$pids = Products::getRandomProductIds(
    rand(1, 5)
);

foreach ($pids as $pid){
    $qty = rand(0, 10);

    if ($qty > 0){
        $products[] = [
            'pid' => $pid,
            'qty' => $qty
        ];    
    }
}

$billing_address = array(
	'first_name' => 'Pablo',
	'last_name'  => 'Bozzolo',
	'company'    => 'Solucion Binaria',
	'email'      => 'info@solucionbinaria.com',
	'phone'      => '+5117019002',
	'address_1'  => '123 calle la loca',
	'address_2'  => '104',
	'city'       => 'Santiago',
	'state'      => 'Santiago',
	'postcode'   => '92121',
	'country'    => 'CL'
);


$order = Orders::createOrder($products, $billing_address, $billing_address);

dd($order);

$orden_id = $order->get_order_number();
dd($orden_id, 'ORDER ID');

//$order->update_status('processing');
