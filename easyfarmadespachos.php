<?php

/**
 * Plugin Name: EasyFarma Despachos
 * Plugin URI:  
 * Description: Info de despachos
 * Version:     1.0.0
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
