<?php

namespace boctulus\EasyFarmaDespachos\libs;

use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Products;

require_once __DIR__ . '/Files.php';	
require_once __DIR__ . '/Products.php';

/*
    EasyFarma attributes reactor
*/

class Reactor
{
	protected $att_name_keys = [];
	protected $att_name_vals = [];

	function __construct()
	{
		add_action('woocommerce_update_product', [$this, 'sync_on_product_update'], 11, 1 );
		add_action('added_post_meta', [$this, 'sync_on_new_post_data'], 10, 4 );
		add_action('untrash_post', [$this, 'sync_on_untrash_post'], 10, 1);

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
			'req_receta'			=> 'Requiere receta'
		];
		
		$this->att_name_keys = array_keys($att_names);   // keys = names
		$this->att_name_vals = array_values($att_names); // vals = labels
	}	

    /*
		Event Hooks
	*/
	
	function onCreate($product){
		$pid = $product->get_id();
		
		if (!empty(get_transient('product-'. $pid))){
			return;
		}

		set_transient('product-'. $pid, true, 2);

		$atts = [];

		foreach ($this->att_name_vals as $at){
			$atts[] = Products::getCustomAttr($pid, $at);
		}

		Files::localDump([
			'event' => 'CREATE',
			'obj' => $atts
		]);
	}

	function onUpdate($product){
		$pid = $product->get_id();			

		if (!empty(get_transient('product-'. $pid))){
			return;
		}

		set_transient('product-'. $pid, true, 2);
		
		$atts = [];
		foreach ($this->att_name_vals as $at){
			$__at = Products::getCustomAttr($pid, $at);

			if ($__at['is_variation'] != 0){
				continue;
			}

			$atts[] = $__at;
		}

		$prev_atts = get_transient('att-product-'. $pid);
		
		if (!empty($prev_atts)){
			// Voy a obtener la diferencia
		}

		/*
			Deber'ia.... sino hay transientes de este tipo, crearlos todos juntos antes 
			de tener que utilizarlos ac'a
		*/
		set_transient('att-product-'. $pid, true, 0);

		Files::localDump([
			'event' => 'UPDATE',
			'obj' => $atts
		]);
	}

	function onDelete($product){
		$pid = $product->get_id();			
		
		$atts = [];

		foreach ($this->att_name_vals as $at){
			$atts[] = Products::getCustomAttr($pid, $at);
		}

		Files::localDump([
			'event' => 'DELETE',
			'obj' => $atts
		]);
	}

	function onRestore($product){
		$pid = $product->get_id();
		// $sku = $product->get_sku();

		// if ($sku == null){
		// 	return;
		// }

		// Files::localDump([
		// 	'event' => 'RESTORE',
		// 	'obj' => Products::dumpProduct($product)
		// ]);
		// ..
	}
	
	//////////////////////////////////////////////


	function sync_on_product_update($product_id) {
		$this->action = 'edit';
		$product = wc_get_product( $product_id );
		$this->onUpdate($product);
	}

	function sync_on_untrash_post($pid){
		if (get_post_type($pid) != 'product'){
			return;
		}

		$this->action = 'untrash';
		$product = wc_get_product($pid);
		$this->onRestore($product);
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
					$this->onDelete($product);
					break;
				case '_wp_old_slug':
					$this->action = 'restore';
					$this->onRestore($product);
					break;
				case '_stock':
					$this->action = 'edit';
					$this->onUpdate($product);
					break;
				// creación
				default:
					$this->action = 'create';
					$this->onCreate($product);
			}
		}
	
	}
}