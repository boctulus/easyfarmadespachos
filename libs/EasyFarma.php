<?php declare(strict_types=1);

namespace boctulus\EasyFarmaDespachos\libs;

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
        Duplica el producto como oculto y con el precio = precio_plus

        Ademas el titulo es alterado haciendo un append de " | EasyFarma Plus"

        No se copian atributos
    */
    static function duplicate_as_hidden($pid, bool $overwrite = false){
        $p = \wc_get_product($pid);

        $name        = $p->get_title();
        $precio_plus = isset($_POST['precio_plus']) ?  $_POST['precio_plus'] : Products::getMeta($pid, 'precio_plus');
        $sku         = $p->get_sku();

        if (!Products::productExists("{$sku}_2"))
        {
            if (Strings::endsWith('_2', $sku)){
                return;
            }

            $p = Products::duplicate($pid, function ($old_sku){
                return "{$old_sku}_2";
            }, [
                'name' => "$name | EasyFarma Plus",
                'regular_price' => $precio_plus,
                'price' => $precio_plus 
            ]);

            Products::hide($p);
            update_post_meta($p->get_id(), 'ori_id',  $pid);
            update_post_meta($p->get_id(), 'ori_sku', $sku);

            return $p;
        } else {
            $dupe_id = Products::getProductIdBySKU("{$sku}_2");

            Products::updatePrice($dupe_id, $precio_plus);
        } 
    }
}