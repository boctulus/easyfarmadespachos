<?php

use boctulus\EasyFarmaDespachos\libs\Products;
use boctulus\EasyFarmaDespachos\libs\Url;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Arrays;
use boctulus\EasyFarmaDespachos\libs\EasyFarma;

require_once __DIR__ . '/libs/Url.php';
require_once __DIR__ . '/libs/Files.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Arrays.php';

/*
    Hook checkout
*/

add_action( 'woocommerce_before_checkout_form', 'scripts_ezfarma_checkout' );

function scripts_ezfarma_checkout(){
    ?>
    <script>
        const SITE_URL = '<?= get_site_url(); ?>';

        function getBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result);
                reader.onerror = error => reject(error);
            });
        }

        // alterativa antigue a getBase64()
        makeblob = function (dataURL) {
            var BASE64_MARKER = ';base64,';
            if (dataURL.indexOf(BASE64_MARKER) == -1) {
                var parts = dataURL.split(',');
                var contentType = parts[0].split(':')[1];
                var raw = decodeURIComponent(parts[1]);
                return new Blob([raw], { type: contentType });
            }
            var parts = dataURL.split(BASE64_MARKER);
            var contentType = parts[0].split(':')[1];
            var raw = window.atob(parts[1]);
            var rawLength = raw.length;

            var uInt8Array = new Uint8Array(rawLength);

            for (var i = 0; i < rawLength; ++i) {
                uInt8Array[i] = raw.charCodeAt(i);
            }

            return new Blob([uInt8Array], { type: contentType });
        }

        function send_file(){
            let file = document.querySelector('#file').files[0];

            getBase64(file).then(
                data => {
                    //console.log(data)

                    // Enviar por Ajax ... por POST
                    jQuery.ajax({
                        url: SITE_URL + '/wp-json/ez_files_base64/v1/post',
                        type: 'POST',
                        processData: false,
                        contentType: 'application/octet-stream',
                        // data es 'data:image/jpeg;base64,9j/4AAQSkZJRgA..........gAooooAKKKKACiiigD//Z'
                        data: data
                    })
                    .done(function(data) {
                        console.log(data);
                        addNotice("Receta recibida", "info", "ef_checkout_messages");
                        console.log("success");
                    })
                    .fail(function(data) {
                        console.log(data);
                        addNotice("Error al recibir receta", "danger", "ef_checkout_messages");
                        console.log("error");
                    });
                }
            );
        }
    </script>
    <?php
}


function table_gen(Array $names){
    $trs = [];

    foreach ($names as $name){
        $trs[] = 
        "<tr>
            <th scope=\"row\">$name</th>
        </tr>";
    }

    $trstr = implode("\r\n", $trs);
    
    return '<table class="dcf-table dcf-table-responsive dcf-table-bordered dcf-table-striped dcf-w-100%">
        <caption>Medicamentos con receta</caption>
        <thead>
            <tr>
                <td>Nombre</td>
            </tr>
        </thead>
        <tbody>'
            .$trstr.
        '</tbody>
    </table>';
}


add_action( 'woocommerce_after_order_notes', 'add_ezfarma_custom_fields' );

function add_ezfarma_custom_fields($checkout)
{
    $items = WC()->cart->get_cart();

    $req_receta_ay = [];
    foreach ($items as $item => $values)
    {
        $qty  = $values['quantity'];
        $pid  = $values['product_id'];
        $sku  = $values['data']->get_sku();
        $name = $values['data']->get_name();

        if (EasyFarma::isSkuPlus($sku)){
            $pid = EasyFarma::getNonPlusProductId($sku);
        }

        $req = Products::getMeta($pid, 'requiere_receta');

        if ($req == 'Si' || $req == 'Sí'){
            $req_receta_ay[] = [
                'pid'  => $pid,
                'name' => $name
            ];
        }
    }

    if (empty($req_receta_ay)){
        return;
    }

    $names = array_column($req_receta_ay, 'name');

    ?>
    <h3>Receta adjunta</h3>

    <?php
        echo table_gen($names);
    ?>

    <label><strong>* Hay medicamentos que requieren que adjunte receta médica.</strong></label>

    <p></p>
    <p style="margin-top:2em" class="form-row form-row-wide validate-required validate-phone">
        <label for="file" class="">Adjunto&nbsp;
            <!-- abbr class="required" title="obligatorio">*</abbr></label -->
            <span class="woocommerce-input-wrapper">
                <input type="file" class="input-text " name="file" id="file" accept="image/*,application/pdf" /> <!-- accept='image/*' -->
            </span>
    </p>

    <button onclick="send_file();" type="button" id="adjuntar_recetas">Adjuntar</button>
    <p></p>

    <div style="margin-top:1em" id="ef_checkout_messages"></div>
    <?php
}

/**
 * Process the checkout
 */
add_action('woocommerce_checkout_process', 'ezfarma_custom_field_process');

function ezfarma_custom_field_process() {
	Files::dump([
        'filesss' => $_FILES
    ]);
}


// Save Image data as order item meta data
add_action( 'woocommerce_checkout_create_order_line_item', 'custom_field_update_order_item_meta', 20, 4 );
function custom_field_update_order_item_meta( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['file_upload'] ) ){
        $item->update_meta_data( '_img_file',  $values['file_upload'] );
    }
}