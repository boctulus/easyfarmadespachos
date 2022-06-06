<?php

use boctulus\EasyFarmaDespachos\libs\Url;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Arrays;


require_once __DIR__ . '/libs/Url.php';
require_once __DIR__ . '/libs/Files.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Arrays.php';

/*
    Hook checkout
*/


if ($_SERVER['REQUEST_URI'] == '/checkout/'):
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
                        console.log("success");
                    })
                    .fail(function(data) {
                        console.log(data);
                        console.log("error");
                    });
                }
            );
        }
	</script>
<?php
endif;


add_action( 'woocommerce_after_order_notes', 'add_ezfarma_custom_fields' );

function add_ezfarma_custom_fields($checkout)
{
    ?>
    <h3>Receta adjunta</h3>

    <label><strong>El medicamento requiere que adjunte receta médica.</strong></label>

    <p></p>
    <p class="form-row form-row-wide validate-required validate-phone">
        <label for="file" class="">Adjunto&nbsp;
            <!-- abbr class="required" title="obligatorio">*</abbr></label -->
            <span class="woocommerce-input-wrapper">
                <input type="file" class="input-text " name="file" id="file" accept='image/*' />
            </span>
    </p>

    <button onclick="send_file();" type="button">Adjuntar</button>

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