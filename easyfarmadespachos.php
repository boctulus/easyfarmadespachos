<?php

/**
 * Plugin Name: EasyFarma Despachos
 * Plugin URI:  
 * Description: Info de despachos
 * Version:     1.0.1
 * Author:      Pablo Bozzolo
 * Author URI:  
 * License:     
 * Text Domain: despachos
 * Domain Path: /languages/
 *
 * @package    EasyFarma
 * @subpackage EasyFarma Despachos
 */

defined('ABSPATH') || die;

require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/installer/easyfarma_files.php';
require_once __DIR__ . '/checkout.php';
require_once __DIR__ . '/libs/Debug.php';

if (!function_exists('dd')){
	function dd($val, $msg = null, $pre_cond = null){
		boctulus\EasyFarmaDespachos\libs\Debug::dd($val, $msg, $pre_cond);
	}
}


// https://generatewp.com/post-type/
if (!function_exists('despachos_post_type')) {

	// Register Custom Post Type
	function despachos_post_type()
	{

		$labels = array(
			'name'                  => _x('Despachos', 'Post Type General Name', 'text_domain'),
			'singular_name'         => _x('Despacho', 'Post Type Singular Name', 'text_domain'),
			'menu_name'             => __('Despachos', 'text_domain'),
			'name_admin_bar'        => __('Despachos', 'text_domain'),
			'archives'              => __('Archivo de data de despacho', 'text_domain'),
			'attributes'            => __('Atributos de ficha de despacho', 'text_domain'),
			'parent_item_colon'     => __('Padre de la ficha de despacho:', 'text_domain'),
			'all_items'             => __('Todas las fichas de despacho', 'text_domain'),
			'add_new_item'          => __('Agregar nueva ficha de despacho', 'text_domain'),
			'add_new'               => __('Agregar nueva ficha', 'text_domain'),
			'new_item'              => __('Nueva ficha de despacho', 'text_domain'),
			'edit_item'             => __('Editar ficha de despacho', 'text_domain'),
			'update_item'           => __('Actualizar ficha de despacho', 'text_domain'),
			'view_item'             => __('Ver ficha de despacho', 'text_domain'),
			'view_items'            => __('Ver fichas de despachos', 'text_domain'),
			'search_items'          => __('Buscar ficha de despacho', 'text_domain'),
			'not_found'             => __('No encontrada', 'text_domain'),
			'not_found_in_trash'    => __('No encontrada en papelera', 'text_domain'),
			'featured_image'        => __('Imágen destacada', 'text_domain'),
			'set_featured_image'    => __('Configurar imágen destacada', 'text_domain'),
			'remove_featured_image' => __('Remover imágen destacada', 'text_domain'),
			'use_featured_image'    => __('Usar como imágen destacada', 'text_domain'),
			'insert_into_item'      => __('Insertar en la ficha de despacho', 'text_domain'),
			'uploaded_to_this_item' => __('Subido a esta ficha de despacho', 'text_domain'),
			'items_list'            => __('Lista de fichas de despacho', 'text_domain'),
			'items_list_navigation' => __('Lista navegable de fichas de despachos', 'text_domain'),
			'filter_items_list'     => __('Filtro de fichas de despachos', 'text_domain'),
		);

		$args = array(
			'label'                 => __('Despacho', 'text_domain'),
			'description'           => __('Información de reparto', 'text_domain'),
			'labels'                => $labels,
			'supports'              => array('title', 'editor', 'comments', 'revisions', 'custom-fields'),
			'taxonomies'            => array('category', 'post_tag'),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-archive',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);

		register_post_type('despachos', $args);
	}

	add_action('init', 'despachos_post_type', 0);
}


// ADDING COLUMNS WITH THEIR TITLES
add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 20 );

function custom_shop_order_column($columns)
{
    $reordered_columns = array();

    // Inserting columns to a specific location
    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if( $key ==  'order_status' ){
            // Inserting after "Status" column
            $reordered_columns['download-prescription'] = __( 'Receta','theme_domain');
			$reordered_columns['print-qr'] = __( 'Código QR','theme_domain');
            $reordered_columns['signature'] = __( 'Firma cliente','theme_domain');
        }
    }
    return $reordered_columns;
}

function getFirmaFilename($order_id){
	// El nombre podr'ia provenir de la base de datos
	return 'firma-order_id-' . $order_id . '.png'; 
}

function getRecetaFilename($order_id){
	// El nombre podr'ia provenir de la base de datos
	return 'receta-order_id-' . $order_id . '.png'; 
}

function getReceta($order_id){
	// Sino existe... debe crearse en alg'un punto
	$path = __DIR__ . '../../wp-content/uploads/easyfarmadespachos/';
	$file = $path . getRecetaFilename($order_id); 

	return file_get_contents($path);
}

if (isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order'):
	?>
	<script>
		function open_qr(e, order_id){
			e.stopImmediatePropagation();
			e.preventDefault();			
			alert('QR para order ' + order_id);
			return false;
		}
	</script>
<?php
endif;

// Adding the data for the additional column
add_action( 'manage_shop_order_posts_custom_column' , 'custom_orders_list_column_content', 10, 2 );

function custom_orders_list_column_content( $column, $order_id )
{
	global $config;

	$path = __DIR__ . '../../wp-content/uploads/easyfarmadespachos/';

    switch($column)
    {
		case 'print-qr':
			echo "<input type='button' onclick='open_qr(event, $order_id);' value=' Ver ' />";

			break;
		case 'download-prescription':			
			
			$anchor = 'Receta escaneada';
			$url    = $path . getRecetaFilename($order_id);  

			echo "<a href='$url' alt='receta escaneada'>$anchor</a>";

			break;
		case 'signature':

			$file = getFirmaFilename($order_id);
			
			// el path debe existir
			$file = get_site_url() . '/wp-content/uploads/easyfarmadespachos/' . $file;
			echo "<img src='$file' width=200 height=100 />";
			
			break;
    }
}