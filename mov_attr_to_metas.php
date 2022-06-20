<?php

use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Products;
// ...

/*
    Objetivo:

    Mover atributos de productos simples a metas compatibles con meta-boxes
*/


if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

	require_once ABSPATH . '/wp-config.php';
	require_once ABSPATH .'/wp-load.php';
}

require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Products.php';


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


$att_equiv = [
    'Laboratorio' => 'laboratorio',
    'Enfermedades' => 'enfermedades',
    'Bioequivalente' => 'bioequivalente',
    'Principio activo' => 'principio_activo',
    'Forma farmacéutica' => 'forma_farmaceutica',
    'Control de Stock' => 'control_de_stock',
    'Otros medicamentos' => 'otros_medicamentos',
    'Dosis' => 'dosis',
    'Código ISP' => 'codigo_isp',
    'Es medicamento' => 'es_medicamento',
    'Mostrar descripción' => 'mostrar_descripcion',
    'Precio por fracción' => 'precio_por_fraccion',
    'Precio por 100 ml o 100 G' => 'precio_por_100',
    'Requiere receta' => 'requiere_receta',
];


$products = wc_get_products([
    //'status' => 'publish', 
    'limit' => -1
]);

foreach ($products as $_p){
    $pid = $_p->get_id();
    $p = Products::getProduct($pid);

    /*
        array (
            'name' => 'Es medicamento',
            'value' => 'Si',
            'position' => 1,
            'is_visible' => 1,
            'is_variation' => 0,
            'is_taxonomy' => 0,
        ),
        // ...
    */

    $atts = Products::getCustomAttr($pid);

    foreach($atts as $att){
        $name = $att['name'];
        $dato = $att['value'];

        $meta_key = $att_equiv[$name];
        
        dd($dato, $meta_key);

        // Salvo
        update_post_meta( $pid, "_{$meta_key}", $dato );

        // Destruyo el atributo
        Products::removeAllAttributesForSimpleProducts($pid);
    } 

}











