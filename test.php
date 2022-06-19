<?php

use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Products;
use boctulus\EasyFarmaDespachos\libs\Users; ///
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
require_once __DIR__ . '/libs/EasyFarma.php'; ///


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


$pid = 8947;

$atts = [
    'Laboratorio' => 'Superlab',
    'Enfermedades' => '',
    'Bioequivalente' => '',
    'Principio activo' => 'Cafeína|Clorfenamina|Ergotamina|Metamizol',
    'Forma farmacéutica' => 'Comprimidos',
    'Control de Stock' => 'Disponible',
    'Otros medicamentos' => 'Fredol|Migragesic|Ultrimin|Migratan|Cefalmin|Cinabel|Migranol|Migra-Nefersil|Tapsin m|Sevedol',
    'Dosis' => '100/4/1/300 mg',
    'Código ISP' => 'F-9932/16',
    'Es medicamento' => 'Si',
    'Mostrar descripción' => 'No',
    'Precio por fracción' => '99',
    'Precio por 100 ml o 100 G' => '',
    'Requiere receta' => 'Si',
];

Products::setProductAttributesForSimpleProducts($pid, $atts);

exit;


$pid = 7843;
#$pid = 8947; // no tiene plus


dd(
    EasyFarma::get_laboratorio($pid)
);


// $pid = 8;

// dd(
//     Users::hasRole('easyfarma_vip', $pid)
// );

exit; /////////

$attrs = [
    // 'pa_color' => array(
    //     'term_names' => array('Red', 'Blue'),
    //     'is_visible' => true,
    //     'for_variation' => false,
    // ),
    // 'pa_size' =>  array(
    //     'term_names' => array('X Large'),
    //     'is_visible' => true,
    //     'for_variation' => false,
    // ),
    'pa_att_prueba' =>  array(
        'term_names' => array('Prueba 1', 'Prueba 2'),
        'is_visible' => true,
        'for_variation' => false,
    ),
];

Products::insertAttTerms($attrs, false);

dd('-- FIN --');






