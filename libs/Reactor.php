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
	protected $action;

	function __construct()
	{
		add_action('woocommerce_update_product', [$this, 'sync_on_product_update'], 11, 1 );
		add_action('added_post_meta', [$this, 'sync_on_new_post_data'], 10, 4 );
		add_action('untrash_post', [$this, 'sync_on_untrash_post'], 10, 1);
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

		Files::localDump([
			'event' => 'CREATE',
			'obj' => Products::dumpProduct($product)
		]);
	}

	function onUpdate($product){
		$pid = $product->get_id();	

		if (!empty(get_transient('product-'. $pid))){
			return;
		}

		set_transient('product-'. $pid, true, 2);
		
		Files::localDump([
			'event' => 'UPDATE',
			'obj' => Products::dumpProduct($product)
		]);

		
	}

	function onDelete($product){
		$pid = $product->get_id();

		Files::localDump([
			'event' => 'DELETE',
			'obj' => Products::dumpProduct($product)
		]);
	}

	function onRestore($product){
		$pid = $product->get_id();
	
		Files::localDump([
			'event' => 'RESTORE',
			'obj' => Products::dumpProduct($product)
		]);
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