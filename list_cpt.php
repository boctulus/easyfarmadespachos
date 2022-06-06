<?php

use boctulus\EasyFarmaDespachos\libs\CPT;
use boctulus\EasyFarmaDespachos\libs\Debug;
// ...


if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

	require_once ABSPATH . '/wp-config.php';
	require_once ABSPATH .'/wp-load.php';
}

require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/CPT.php';


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


$post = CPT::getOne('despachos');
$pid  = (int) $post['ID'];

$post['estado_envio'] = $att = get_post_meta($pid, 'Estado del envio', true);
$post['coordenadas'] = $att = get_post_meta($pid, 'Coordenadas del repartidor', true);
$post['nota_repartidor'] = $att = get_post_meta($pid, 'Nota repartidor', true);
$post['nota_admin'] = $att = get_post_meta($pid, 'Nota admin', true);

dd(
	$post
);
