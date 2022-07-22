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

use boctulus\EasyFarmaDespachos\libs\Reactor;

defined('ABSPATH') || die;

#if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
#}


$config = include __DIR__ . '/config/config.php';

require_once __DIR__ . '/installer/easyfarma_files.php';
require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Reactor.php'; // Reactor
require_once __DIR__ . '/libs/Users.php';
require_once __DIR__ . '/libs/Url.php';

require_once __DIR__ . '/helpers/debug.php';
require_once __DIR__ . '/helpers/cli.php';

require_once __DIR__ . '/ajax.php';

require_once __DIR__ . '/checkout.php'; // hooks
require_once __DIR__ . '/cond_pricing.php'; // hooks

require_once __DIR__ . '/meta_box_despachos.php';
require_once __DIR__ . '/meta_box_productos.php';

use boctulus\EasyFarmaDespachos\libs\Url;

function my_css_enqueues() 
{  
	//if (!is_home()){
		wp_register_script('bootstrap', plugin_dir_url(__FILE__) . '/assets/js/bootstrap/bootstrap.bundle.min.js');
		wp_enqueue_script('bootstrap');

		wp_register_style('bootstrap', plugin_dir_url(__FILE__) . '/assets/css/bootstrap/bootstrap.min.css');
		wp_enqueue_style('bootstrap');

		wp_register_style('ef_main',  plugin_dir_url(__FILE__) . '/assets/css/ef_styles.css');
		wp_enqueue_style('ef_main');

		?>
			<script>
				function addNotice(message, type = 'info', id_container = 'alert_container', replace = false){
					let types = ['info', 'danger', 'warning', 'success'];

					if (jQuery.inArray(type, types) == -1){
						throw "Tipo de notificación inválida para " + type;
					}

					if (message === ""){
						throw "Mensaje de notificación no puede quedar vacio";
						return;
					}

					let alert_container  = document.getElementById(id_container);
				
					if (replace){
						alert_container.innerHTML = '';
					}

					let code = (new Date().getTime()).toString();
					let id_notice = "notice-" + code;
					let id_close  = "close-"  + code;

					div = document.createElement('div');			
					div.innerHTML = `
					<div class="alert alert-${type} alert-dismissible fade show mt-3" role="alert" id="${id_notice}">
						<span>
							${message}
						</span>
						<button type="button" class="btn-close notice" data-bs-dismiss="alert" aria-label="Close" id="${id_close}"></button>
					</div>`;

					alert_container.classList.add('mt-5');
					alert_container.prepend(div);

					document.getElementById(id_close).addEventListener('click', () => {
						let cnt = document.querySelectorAll('button.btn-close.notice').length -1;
						if (cnt == 0){
							alert_container.classList.remove('mt-5');
							alert_container.classList.add('mt-3');
						}
					});


					return id_notice;
				}

				function hideNotice(id_container = 'alert_container', notice_id = null){
					if (notice_id == null){
						let div  = document.querySelector(`div#${id_container}`);
						div.innerHTML = '';
						alert_container.classList.remove('mt-5');
					} else {
						document.getElementById(notice_id).remove();
					}
				}

				function clearNotices(id_container = 'alert_container'){
					hideNotice(id_container);
				}
			</script>
		<?php
	//}
}

add_action( 'wp_enqueue_scripts', 'my_css_enqueues');



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
}

/*
	 https://preventdirectaccess.com/wordpress-add-user-role-programmatically/
	 https://developer.wordpress.org/reference/functions/add_role/
*/
function easyfarmadespachos_add_roles(){
	add_role(
		'repartidor', //  System name of the role.
		__( 'Repartidor'  ), // Display name of the role.
		array(
			'read'  => true,
			'delete_posts'  => false,
			'delete_published_posts' => false,
			'edit_posts'   => false,
			'publish_posts' => true,
			'upload_files'  => false,
			'edit_pages'  => false,
			'edit_published_pages'  =>  false,
			'publish_pages'  => false,
			'delete_published_pages' => false, 
		)
	);

	add_role(
		'easyfarma_vip', //  System name of the role.
		__( 'EasyFarma VIP'  ), // Display name of the role.
		array(
			'read'  => true,
			'delete_posts'  => false,
			'delete_published_posts' => false,
			'edit_posts'   => false,
			'publish_posts' => false,
			'upload_files'  => false,
			'edit_pages'  => false,
			'edit_published_pages'  =>  false,
			'publish_pages'  => false,
			'delete_published_pages' => false, 
		)
	);
}


function easyfarmadespachos_init(){
	despachos_post_type();
	easyfarmadespachos_add_roles();
}

add_action('init', 'easyfarmadespachos_init', 0);


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


/*
	Instancio reactor
*/


#if ($config['sync_attr']){
	$reactor = new Reactor();
#}
