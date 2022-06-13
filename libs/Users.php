<?php

/*
    @author Pablo Bozzolo boctulus@gmail.com
*/

namespace boctulus\EasyFarmaDespachos\libs;

use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Arrays;

class Users
{
    static function roleExists( $role ) {
        if( ! empty( $role ) ) {
            return $GLOBALS['wp_roles']->is_role( $role );
        }

        return false;
    }
}