<?php

/*
    METABOX para Despachos (hacer tambi'en para productos)

    https://www.sitepoint.com/adding-meta-boxes-post-types-wordpress/
*/

function fichas_despacho_meta_box() {

    $screens = array( 'despachos' );

    foreach ( $screens as $screen ) {
        add_meta_box(
            'despachos-coord-lat',
            'Latitud',
            'lat_meta_box_callback',
            $screen
        );

        add_meta_box(
            'despachos-coord-lon',
            'Longitud',
            'lon_meta_box_callback',
            $screen
        );
        
        add_meta_box(
            'despachos-nota_repartidor',
            'Nota del repartidor',
            'nota_repartidor_meta_box_callback',
            $screen
        );
    }
}

add_action( 'add_meta_boxes', 'fichas_despacho_meta_box' );

add_action( 'add_meta_boxes', 'fichas_despacho_meta_box' );

function lat_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'despachos_nonce', 'despachos_nonce' );

    $value = get_post_meta( $post->ID, '_coord_lat', true );

    echo '<textarea style="width:100%" id="coord_lat" name="coord_lat">' . esc_attr( $value ) . '</textarea>';
}

function lon_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'despachos_nonce', 'despachos_nonce' );

    $value = get_post_meta( $post->ID, '_coord_lon', true );

    echo '<textarea style="width:100%" id="coord_lon" name="coord_lon">' . esc_attr( $value ) . '</textarea>';
}

function nota_repartidor_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'despachos_nonce', 'despachos_nonce' );

    $value = get_post_meta( $post->ID, '_nota_repartidor', true );

    echo '<textarea style="width:100%" id="nota_repartidor" name="nota_repartidor">' . esc_attr( $value ) . '</textarea>';
}


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function save_fichas_despacho_meta_box_data( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['despachos_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['despachos_nonce'], 'despachos_nonce' ) ) {
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
    if ( ! isset( $_POST['nota_repartidor'] ) ) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field( $_POST['coord_lon'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_coord_lon', $my_data ); 


    // Sanitize user input.
    $my_data = sanitize_text_field( $_POST['coord_lat'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_coord_lat', $my_data ); 


    // Sanitize user input.
    $my_data = sanitize_text_field( $_POST['nota_repartidor'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_nota_repartidor', $my_data ); 
}

add_action( 'save_post', 'save_fichas_despacho_meta_box_data' );



