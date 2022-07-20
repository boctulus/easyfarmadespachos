<?php declare(strict_types=1);

namespace boctulus\EasyFarmaDespachos\libs;

/*
	@author boctulus
*/

class Carrito 
{
	static function find($product_id){
		$product_cart_id = WC()->cart->generate_cart_id( $product_id );
   		$cart_item_key   = WC()->cart->find_product_in_cart( $product_cart_id );

		return $cart_item_key;
	}

	static function setQuantity($product_id, int $qty, $variation_id = '', $variation = [], string $cart_item_key = '')
	{
		if (empty($cart_item_key)){
			$cart_item_key = static::find($product_id);
		}

		// Remuevo producto original
		WC()->cart->remove_cart_item($cart_item_key);

		// Agrego la cantidad deseada
		WC()->cart->add_to_cart($product_id, $qty, $variation_id, $variation, $cart_item_key);
	}

}