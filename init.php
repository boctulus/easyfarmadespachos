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

require_once __DIR__ . '/helpers/debug.php';


global $wpdb;

$cli = (php_sapi_name() == 'cli');


if (!$cli){
	echo "Ejecutar desde la terminal";
}

EasyFarma::initDuplication();
exit;////