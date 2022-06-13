<?php declare(strict_types=1);

namespace boctulus\EasyFarmaDespachos\libs;

/*
	@author boctulus
*/

class EasyFarma 
{
    static function get_precio_plus($pid){
        $arr = Products::getCustomAttr($pid, 'Precio EasyFarma Plus');
    
        if ($arr === null){
            return;
        }
    
        return (float) $arr['value'];
    }
    
}