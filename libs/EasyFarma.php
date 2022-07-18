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
        $precio_plus = Products::getMeta($pid, 'precio_plus');
        $sku         = $p->get_sku();

        if ($overwrite){
            dd("{$sku}_2");

            if (Products::productExists("{$sku}_2")){
                here();

                dd("Borrando para SKU = {$sku}_2" );
		        Products::deleteProductBySKU("{$sku}_2", true);
            }
        }

        exit;////

        if ($overwrite || !Products::productExists("{$sku}_2"))
        {
            if (Strings::endsWith('_2', $sku)){
                return;
            }

            dd("Duplicando para PID = $pid");

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