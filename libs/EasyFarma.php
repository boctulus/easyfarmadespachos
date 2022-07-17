<?php declare(strict_types=1);

namespace boctulus\EasyFarmaDespachos\libs;

/*
	@author boctulus
*/

class EasyFarma 
{
    /*
        Duplica el producto como oculto y con el precio = precio_plus

        Ademas el titulo es alterado haciendo un append de " | EasyFarma Plus"

        No se copian atributos
    */
    static function duplicate_as_hidden($pid){
        $p = \wc_get_product($pid);

        $name        = $p->get_title();
        $precio_plus = Products::getMeta($pid, 'precio_plus');

        $p = Products::duplicate($pid, false, [
            'name' => "$name | EasyFarma Plus",
            'regular_price' => $precio_plus,
            'price' => $precio_plus 
        ]);

        Products::hide($p);

        return $p;
    }
}