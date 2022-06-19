<?php

/*
    METABOX para Productos

    https://www.sitepoint.com/adding-meta-boxes-post-types-wordpress/
*/

use boctulus\EasyFarmaDespachos\libs\EasyFarma;

function productos_meta_box() {
    $screen = 'product';

    // Laboratorio
    add_meta_box(
        'productos-laboratorio',
        'Laboratorio',
        'laboratorio_meta_box_callback',
        $screen
    );

    // ..
}

add_action( 'add_meta_boxes', 'productos_meta_box' );

function laboratorio_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = EasyFarma::get_laboratorio($post->ID); 

    echo '<textarea style="width:100%" id="laboratorio" name="laboratorio">' . esc_attr( $value ) . '</textarea>';
}

// ...


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function save_productos_meta_box_data( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['productos_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['productos_nonce'], 'productos_nonce' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['laboratorio'] ) ) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field( $_POST['laboratorio'] );

    // update_post_meta( $post_id, '_coord_lon', $my_data ); 
    ////////////// para escribir usar setProductAttributesForSimpleProducts()

}

add_action( 'save_post', 'save_productos_meta_box_data' );



