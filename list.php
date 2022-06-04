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

exit;

////
//  OK

$pid = 7853;
$att = get_post_meta($pid, 'Nota repartidor', true);

dd($att);

exit;
///

$products = wc_get_products([]);

/*
	--[ PRODUCTO ID 51 ]-- 
	array (
	'sku' => '7800007112743',
	'precio' => '2990',
	'precio_plus' => '1604',
	'laboratorio' => 'Laboratorio Chile',
	'enfermedades' => 
	array (
		0 => 'Herpes',
	),
	'bioequivalente' => 
	array (
		0 => 'BEaciclovir200',
	),
	'principio_activo' => 
	array (
		0 => 'Aciclovir',
	),
	'forma_farmaceutica' => 'Comprimidos',
	'control_de_stock' => 'Disponible',
	'otros_medicamentos' => 
	array (
		0 => 'Lisovyr',
	),
	'dosis' => '200  mg',
	)

*/
foreach ($products as$p){
	$pid =$p->get_id();

	dd([
		'sku' =>$p->get_sku(),
		'precio' => $p->get_regular_price(),
		'precio_plus' => $p->get_sale_price(),

		'laboratorio'  =>$p->get_meta("laboratorio"),
		'enfermedades'  =>$p->get_meta("enfermedades")	,
		'bioequivalente'  =>$p->get_meta("bioequivalente")	,
		'principio_activo'  =>$p->get_meta("principio_activo")	,
		'forma_farmaceutica'  =>$p->get_meta("forma_farmaceutica")	,
		'control_de_stock'  =>$p->get_meta("control_de_stock")	,
		'otros_medicamentos'  =>$p->get_meta("otros_medicamentos")	,
		'dosis' =>$p->get_meta("dosis")		
	], "PRODUCTO ID $pid"); 
}









