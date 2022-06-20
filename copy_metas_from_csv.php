<?php

use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Products;
// ...

/*
    Objetivo:

    Copiar metas compatibles con meta-boxes y precio EasyFarma Plus desde CSV
*/

ini_set("memory_limit","728M");

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

	require_once ABSPATH . '/wp-config.php';
	require_once ABSPATH .'/wp-load.php';
}

require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Files.php';
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
    'Precio EasyFarma Plus' => 'precio_plus'
];


$path =  __DIR__ . '/completo_keys_updated.csv';

$rows = Files::getCSV($path)['rows'];

foreach ($rows as $row) {
    // if ($row['sku'] != 'C8903726249093'){
    //     continue;
    // }

    $pid = Products::getProductIdBySKU($row['sku']);

    foreach($att_equiv as $csv_key => $meta_key){
        if (isset($row[$csv_key]))
        {
            // De momento solo me interesa traerme el precio_plus
            if ($meta_key != 'precio_plus'){
                continue;
            }

            $dato = $row[$csv_key];

            dd([
                'pid' => $pid,
                'key' => "_{$meta_key}",
                'val' => $dato
            ]);

            update_post_meta( $pid, "_{$meta_key}", $dato );
        } else {
           // dd("campo '$csv_key' no encontrado");
        }
    }

}








