<?php

use boctulus\EasyFarmaInit\libs\Debug;
use boctulus\EasyFarmaInit\libs\Products;
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

function addNewTaxonomies(){
	$new_atts = [
		[
			'Dosis', 'dosis'
		],
		[
			'Código ISP', 'codigo_isp'
		],
		[
			'Requiere receta', 'req_receta'
		],
		[
			'Laboratorio', 'laboratorio'
		],
		[
			'Precio por 100 ml o 100 G', 'precio_x100'
		],
		[
			'Precio por fracción', 'precio_fraccion'
		],
		[
			'Enfermedades', 'enfermedades'
		],
		[
			'Mostrar descripción', 'mostrar_descr'
		],
		[
			'Es medicamento', 'es_medicamento'
		],
		[
			'Otros medicamentos', 'otros_medicamentos'
		],
		[
			'Principio activo', 'principio_activo'
		],
		[
			'Forma farmacéutica', 'forma_farmaceutica'
		],
		[
			'Bioequivalente', 'bioequivalente'
		],	
		[
			'Control de Stock', 'control_de_stock'
		],
		[
			'Precio EasyFarma Plus', 'precio_easyfarma_plus'
		]
	];


	foreach ($new_atts as $new_at){
		Products::createAttributeTaxonomy($new_at[0], $new_at[1]);
	}


	dd(Products::getAttributeTaxonomies());
}


addNewTaxonomies();
echo 'OK';

