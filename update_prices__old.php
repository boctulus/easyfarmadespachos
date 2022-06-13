<?php

/*
    Script que convierte el sale_price en precio EasyFarma Plus (custom attr)

    Usar una sola vez ********

    <-- es destructivo si se repite la operaci'on

    ANTES: correr

    add_taxonomies.php
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

echo "Current Memory: " .ini_get("memory_limit")."\r\n";
ini_set("memory_limit","728M");
echo "Updates Memory: ".ini_get("memory_limit")."\r\n";


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


if (!function_exists('dd')){
	function dd($val, $msg = null, $pre_cond = null){
		Debug::dd($val, $msg, $pre_cond);
	}
}


//////////////////////////////
//
// Ya se ejecuto...... no m;as
//
//////////////////////////////

exit;
/////////////////////////////

global $wpdb;

$cli = (php_sapi_name() == 'cli');


if (!$cli){
	echo "Ejecutar desde la terminal";
}


$att_names = [
    'laboratorio' 			=> 'Laboratorio',
    'enfermedades' 			=> 'Enfermedades',
    'bioequivalente' 		=> 'Bioequivalente',
    'principio_activo' 		=> 'Principio activo',
    'forma_farmaceutica' 	=> 'Forma farmacéutica',
    'control_de_stock' 		=> 'Control de Stock',
    'otros_medicamentos'	=> 'Otros medicamentos',
    'dosis' 				=> 'Dosis',
    'codigo_isp' 			=> 'Código ISP',
    'es_medicamento' 		=> 'Es medicamento',
    'mostrar_descr' 		=> 'Mostrar descripción',
    'precio_fraccion' 		=> 'Precio por fracción',
    'precio_x100'			=> 'Precio por 100 ml o 100 G',
    'req_receta'			=> 'Requiere receta',
    'precio_easyfarma_plus' => 'Precio EasyFarma Plus'
];


/*
    Aplica trim() y ucfirst() a... arrays o strings
*/
function t($data){
	if (is_array($data)){
		$data = Arrays::trimArray(array_unique($data));

		$data = array_map('ucfirst', $data);
	} else {		
		$data = trim($data);
		$data = ucfirst($data);
	}
	
	return $data;
}

$products = wc_get_products([
    //'status' => 'publish', 
    'limit' => -1
]);

foreach ($products as $_p){
    $pid = $_p->get_id();
    
    dd($pid, 'PID');

    $p = Products::getProduct($pid);

    $sale_price = $p->get_sale_price();

    /*
        [
            // ..
             12 =>
            array (
                'name' => 'Precio por 100 ml o 100 G',
                'value' => '53',
                'position' => 1,
                'is_visible' => 1,
                'is_variation' => 0,
                'is_taxonomy' => 0,
            ),
            13 =>
            array (
                'name' => 'Requiere receta',
                'value' => 'Si',
                'position' => 1,
                'is_visible' => 1,
                'is_variation' => 0,
                'is_taxonomy' => 0,
            ),
        ]
    */
    $raw_atts = Products::getCustomAttr($pid);

    $atts = [];
    
    if (!empty($raw_atts)){
        foreach($raw_atts as $raw_at){
            $label = $raw_at['name']; // 'Precio EasyFarma Plus'
            $name  = array_search($label, $att_names); // 'precio_easyfarma_plus'
    
            $atts[$name] = $raw_at['value'];
        }
    }

    $atts['precio_easyfarma_plus'] = $sale_price;

    // dd($atts);
    // continue; ///

    $att_name_keys = array_keys($atts);
    $att_name_vals = array_values($att_names);


    $term_arr = [];
	foreach ($att_name_keys as $ix => $at_key){
		//$atts[$at_key] = t($atts[$at_key]);

		$term_names = !is_array($atts[$at_key]) ? [ $atts[$at_key] ] : $atts[$at_key];
	
		// 	dd(
		// 		$term_names, $at_key
		// 	);

		// $attrx = [
		// 	"pa_$at_key" =>  [
		// 		'term_names' => $term_names,
		// 		'is_visible' => true,
		// 		'for_variation' => false,
		// 	],
		// ];

		// Products::createProductAttributes($attrx, false);

		$at_name = $att_name_vals[$ix];
		$term_arr[$at_name] = implode('|', $term_names);
	}

    Products::createProductAttributesForSimpleProducs($pid, $term_arr);

    /*
        Ahora destruyo el sale_price
    */

    Products::removeSalePrice($pid);

    dd(
        Products::getCustomAttr($pid)
    );

    Files::logger("Actualizando PID $pid - cambiando atributos. Precio EasyFarma Plus -> $sale_price");    
} 











