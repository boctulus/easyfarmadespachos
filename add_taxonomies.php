<?php

use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Products;
// ...


if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

	require_once ABSPATH . '/wp-config.php';
	require_once ABSPATH .'/wp-load.php';
}

require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Products.php';
// ...


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

/*
	Creaci'on de atributos re-utilizables de productos. Normalemte se utilizan con productos variables.

	Se crean en la tabla wp_woocommerce_attribute_taxonomies
*/
function addNewTaxonomies(){
	$new_atts = [
		['Laboratorio', 'laboratorio'],
		['Enfermedades', 'enfermedades'],
		['Bioequivalente', 'bioequivalente'],	
		['Principio activo', 'principio_activo'],
		['Forma farmacéutica', 'forma_farmaceutica'],
		['Control de Stock', 'control_de_stock'],
		['Otros medicamentos', 'otros_medicamentos'],
		['Dosis', 'dosis' ],		
		['Código ISP', 'codigo_isp' ],
		['Es medicamento', 'es_medicamento'],
		['Mostrar descripción', 'mostrar_descripcion'], //
		['Precio por fracción', 'precio_por_fraccion'], //
		['Precio por 100 ml o 100 G', 'precio_por_100'], //
		['Requiere receta', 'requiere_receta'], //
		['Precio EasyFarma Plus', 'precio_plus'] //
	];


	foreach ($new_atts as $new_at){
		Products::createAttributeTaxonomy($new_at[0], $new_at[1]);
	}
}


addNewTaxonomies();
dd(
	Products::getCustomAttributeTaxonomies()
);

dd('OK');

