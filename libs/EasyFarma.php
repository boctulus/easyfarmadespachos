<?php declare(strict_types=1);

namespace boctulus\EasyFarmaDespachos\libs;

use boctulus\EasyFarmaDespachos\libs\Carrito;
use ParagonIE\Sodium\Core\Curve25519\Ge\P2;

/*
	@author boctulus
*/

class EasyFarma 
{
    static function getPrecioPlus($pid){
        return Products::getMeta($pid, 'precio_plus');
    }

    static function initDuplication()
    {        
        $pids = Products::getIDs();

        foreach ($pids as $pid){
            $p = EasyFarma::duplicate_as_hidden($pid);

            if ($p != null){
                dd(
                    $p->get_id()
                );
            }
        }
    }

    /*
        Devuelve si el SKU se corresponde a un producto con precio Plus
    */
    static function isSkuPlus($sku, bool $check_for_existance){
        if (Strings::endsWith('_2', $sku)){
            if (!Products::productExists($sku))
            {
                throw new \InvalidArgumentException("SKU '$sku' no existe");
            }

            return true;
        }
    }

    /*
        Devuelve si el product_id se corresponde a un producto con precio Plus
    */
    static function isProductIdPlus($prod_id){
        $sku = Products::getSKUFromProductId($prod_id);

        if (empty($sku)){
            throw new \InvalidArgumentException("SKU '$sku' no existe");
        }

        if (Strings::endsWith('_2', $sku)){
            if (!Products::productExists($sku))
            {
                throw new \InvalidArgumentException("SKU '$sku' no existe");
            }

            return true;
        }
    }

    static function getPlusVersion($prod_id)
    {
        if (static::isProductIdPlus($prod_id)){
            return $prod_id;
        }

        $sku      = Products::getSKUFromProductId($prod_id);
        $sku_plus = "{$sku}_2";

        return Products::getProductIdBySKU($sku_plus);
    }

    /*
        Duplica el producto como oculto y con el precio = precio_plus

        Ademas el titulo es alterado haciendo un append de " | EasyFarma Plus"

        No se copian atributos
    */
    static function duplicate_as_hidden($pid, bool $overwrite = false){
        $p = \wc_get_product($pid);

        $name        = $p->get_title();
        $precio_plus = isset($_POST['precio_plus']) ?  $_POST['precio_plus'] : Products::getMeta($pid, 'precio_plus');
        $sku         = $p->get_sku();

        if (Strings::endsWith('_2', $sku)){
            return;
        }

        if (!Products::productExists("{$sku}_2"))
        {   
            $p = Products::duplicate($pid, function ($old_sku){
                return "{$old_sku}_2";
            }, [
                'name' => "$name | EasyFarma Plus",
                'regular_price' => $precio_plus,
                'price' => $precio_plus 
            ]);

            $dupe_id = $p->get_id();

            Products::hide($p);         

            return $p;
        } else {
            $dupe_id = Products::getProductIdBySKU("{$sku}_2");

            Products::updatePrice($dupe_id, $precio_plus);
        } 

        update_post_meta($dupe_id, 'ori_id',  $pid);
        update_post_meta($dupe_id, 'ori_sku', $sku);
        update_post_meta($dupe_id, 'precio_plus', $precio_plus);
    }


    static function getBuyedQuantityEasyFarmaPlusPerUser($product_id, $user_id = null){
        if ($user_id === null){
            $user_id = get_current_user_id();

            if ($user_id === 0){
                throw new \Exception("User id no puede ser determinado en el contexto actual");
            }
        }

        $orders = Orders::getRecentOrders(30, $user_id);

        $qty = 0;
        foreach ($orders as $order){
            $items = Orders::getOrderItemArray($order);

            foreach ($items as $item_ay){               
                if ($item_ay['product_id'] == $product_id){
                     // chequeo
                    if (!Strings::endsWith('_2', $item_ay['sku'])){
                        throw new \InvalidArgumentException("Product id '$product_id' no corresponde a producto con precio Plus");
                    }

                    $qty += $item_ay['qty'];
                }
            }
        }

        return $qty;
    }

    static function cartLogic(&$cant_en_carrito_plus, &$cant_en_carrito_normal, &$cant_compras_mensuales_plus, $max_abs_plus){       
        if ($cant_compras_mensuales_plus > $max_abs_plus){
            // d([
            //     "Eliminar del carrito *todos* los items plus",
            //     "Agregar al carrito la misma cantidad como normal"
            // ],"Convertir cantidad plus -> normal");
            
            $cant_en_carrito_normal += $cant_en_carrito_plus;
            $cant_en_carrito_plus = 0;
        } else {
            $margen_para_plus = $max_abs_plus - $cant_compras_mensuales_plus;

            // d($margen_para_plus, 'Margen para plus');

            if ($cant_en_carrito_plus <= $margen_para_plus){
                // d("Debo convertir todos los items normales en plus (si el usuario tiene la membresia)");

                $cant_en_carrito_normal -= $margen_para_plus;
                $cant_en_carrito_plus   += $margen_para_plus;

            } else {
                $_dif_plus_y_plus_max = $cant_en_carrito_plus - $margen_para_plus;

                // d([
                //     "Debo dejar la cantidad de $margen_para_plus items plus",
                //     "Incrementar en la cantidad $_dif_plus_y_plus_max como normal"
                // ],"Convertir cantidad plus -> normal");


                $cant_en_carrito_plus = $margen_para_plus;
                $cant_en_carrito_normal += $_dif_plus_y_plus_max;
            }
        }

        $cant_compras_mensuales_plus += $cant_en_carrito_plus;
    }

}