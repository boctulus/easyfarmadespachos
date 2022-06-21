<?php

namespace boctulus\EasyFarmaDespachos\libs;

use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Products;

require_once __DIR__ . '/Files.php';	
require_once __DIR__ . '/Products.php';

/*
    EasyFarma attributes reactor

	Sincroniza metas de metaboxes hacia atributos de productos variables

	@author Pablo Bozzolo (2022)
*/

class Reactor
{
	protected $atts = [
		'Laboratorio' => 'laboratorio',
		'Enfermedades'  => 'enfermedades',
		'Bioequivalente'  =>'bioequivalente',	
		'Principio activo'  => 'principio_activo',
		'Forma farmacéutica' => 'forma_farmaceutica',
		'Control de Stock'  => 'control_de_stock',
		'Otros medicamentos'  => 'otros_medicamentos',
		'Dosis'  => 'dosis',		
		'Código ISP'  => 'codigo_isp',
		'Es medicamento'  => 'es_medicamento',
		'Mostrar descripción'  => 'mostrar_descripcion',
		'Precio por fracción' => 'precio_por_fraccion',
		'Precio por 100 ml o 100 G'  => 'precio_por_100',
		'Requiere receta' => 'requiere_receta', 
		'Precio EasyFarma Plus'  => 'precio_plus'
	];

	protected $action;

	function __construct()
	{
		add_action('woocommerce_update_product', [$this, 'sync_on_product_update'], 10, 2 );
		add_action('added_post_meta', [$this, 'sync_on_new_post_data'], 10, 4 );
		add_action('untrash_post', [$this, 'sync_on_untrash_post'], 10, 1);
	}	

    /*
		Event Hooks
	*/
	
	function onCreate($pid, $product){		
		if (!empty(get_transient('product-'. $pid))){
			return;
		}
		
		set_transient('product-'. $pid, true, 2);

		foreach ($this->atts as $att => $meta_key){
			$new_val = $_POST[$meta_key];  			
			
			/*	
				Si el valor nuevo tiene cuenta de 0 en atributos re-utilizables => agregarlo
			*/
			if (!Products::termExists($new_val, $meta_key)){
				//Files::localLogger("Insertando $meta_key = $new_val");

				$attrs = [
					$meta_key =>  array(
						'term_names' => [
							$new_val
						],
						'is_visible' => true,
						'for_variation' => false,
					),
				];
				
				Products::insertAttTerms($attrs, false);
			}
		
		}
	}

	function onUpdate($pid, $product)
	{
		if (!empty(get_transient('product-'. $pid))){
			return;
		}

		set_transient('product-'. $pid, true, 2);	

		foreach ($this->atts as $att => $meta_key){
			$old_val = Products::getMetasByProduct($pid, $meta_key, true);
			$new_val = $_POST[$meta_key];  
			
			if ($old_val != $new_val){
				$old_val_count = Products::countByMeta($meta_key, $old_val);

				/*
					 Si el valor viejo tenia cuenta de 1 => removerlo
				*/
				if ($old_val_count == 1 && $old_val != ''){
					//Files::localLogger("Eliminando para $meta_key = $old_val");

					Products::deleteTermByName($old_val, $meta_key);
				}

				/*	
					Si el valor nuevo tiene cuenta de 0 en atributos re-utilizables => agregarlo
				*/
				if (!Products::termExists($new_val, $meta_key)){
					//Files::localLogger("Insertando $meta_key = $new_val");

					$attrs = [
						$meta_key =>  array(
							'term_names' => [
								$new_val
							],
							'is_visible' => true,
							'for_variation' => false,
						),
					];
					
					Products::insertAttTerms($attrs, false);
				}
			}
		}

	}

	function onDelete($pid, $product)
	{
		foreach ($this->atts as $att => $meta_key){
			$old_val = Products::getMetasByProduct($pid, $meta_key, true);
			
			if ($old_val == ''){
				continue;
			}

			$old_val_count = Products::countByMeta($meta_key, $old_val);

			/*
				Si el valor viejo tenia cuenta de 1 => removerlo
			*/
			if ($old_val_count == 1){
				Files::localLogger("Eliminando para $meta_key = $old_val");

				Products::deleteTermByName($old_val, $meta_key);
			}
		}
	}

	function onRestore($pid, $product)
	{	
		foreach ($this->atts as $att => $meta_key){
			$val = Products::getMetasByProduct($pid, $meta_key, true);
			
			/*	
				Si el valor nuevo tiene cuenta de 0 en atributos re-utilizables => agregarlo
			*/
			if (!Products::termExists($val, $meta_key)){
				//Files::localLogger("Insertando $meta_key = $new_val");

				$attrs = [
					$meta_key =>  array(
						'term_names' => [
							$val
						],
						'is_visible' => true,
						'for_variation' => false,
					),
				];
				
				Products::insertAttTerms($attrs, false);
			}
			
		}
	}
	
	//////////////////////////////////////////////


	function sync_on_product_update($product_id, $product) {
		$this->action = 'edit';
		$product = wc_get_product( $product_id );
		$this->onUpdate($product_id, $product);
	}

	function sync_on_untrash_post($pid){
		if (get_post_type($pid) != 'product'){
			return;
		}

		$this->action = 'untrash';
		$product = wc_get_product($pid);
		$this->onRestore($pid, $product);
	}

	function sync_on_new_post_data($meta_id, $post_id, $meta_key, $meta_value) {  
		if (get_post_type($post_id) == 'product') 
		{ 
			/*
				$meta_key == 
				
				_wp_trash_meta_status  => es borrado
				_wp_old_slug => restaurado
				_stock => editado
			*/

			// si ya lo cogió el otro hook
			if ($this->action == 'edit'){
				return;
			}

			//  draft y otros no me interesan
			if ($meta_value != 'publish'){
				return;
			}

			$product = wc_get_product( $post_id );

			switch ($meta_key){
				case '_wp_trash_meta_status': 
					$this->action = 'trash';
					$this->onDelete($post_id, $product);
					break;
				case '_wp_old_slug':
					$this->action = 'restore';
					$this->onRestore($post_id, $product);
					break;
				case '_stock':
					$this->action = 'edit';
					$this->onUpdate($post_id, $product);
					break;
				// creación
				default:
					$this->action = 'create';
					$this->onCreate($post_id, $product);
			}
		}
	
	}
}