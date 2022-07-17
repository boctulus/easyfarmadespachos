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

    /*
        Duplica el producto como oculto y con el precio = precio_plus

        Ademas el titulo es alterado haciendo un append de " | EasyFarma Plus"

        No se copian atributos
    */
    static function duplicate_as_hidden($pid){
        $p = \wc_get_product($pid);

        $name        = $p->get_title();
        $precio_plus = Products::getMeta($pid, 'precio_plus');
        $sku         = $p->get_sku();

        if (!Products::productExists("{$sku}_2")){

            $p = Products::duplicate($pid, function ($old_sku){
                return "{$old_sku}_2";
            }, [
                'name' => "$name | EasyFarma Plus",
                'regular_price' => $precio_plus,
                'price' => $precio_plus 
            ]);

            Products::hide($p);

            return $p;
        } 
    }
}