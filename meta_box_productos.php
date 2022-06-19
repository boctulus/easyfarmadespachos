<?php

/*
    METABOX para Productos

    https://www.sitepoint.com/adding-meta-boxes-post-types-wordpress/
*/

use boctulus\EasyFarmaDespachos\libs\EasyFarma;

$meta_atts = [
    'laboratorio',
    'enfermedades',
    'bioequivalente', 
    'principio_activo', 
    'forma_farmaceutica',
    'control_de_stock',
    'otros_medicamentos', 
    'dosis',
    'codigo_isp',
    'es_medicamento',
    'mostrar_descripcion',
    'precio_por_fraccion',
    'precio_por_100',
    'requiere_receta',
];

function productos_meta_box() {
    $screen = 'product';

    // Laboratorio
    add_meta_box(
        'productos-laboratorio',
        'Laboratorio',
        'laboratorio_meta_box_callback',
        $screen
    );

    // Enfermedades
    add_meta_box(
        'productos-enfermedades',
        'Enfermedades',
        'enfermedades_meta_box_callback',
        $screen
    );

    // Bioequivalente
    add_meta_box(
        'productos-bioequivalente',
        'Bioequivalente',
        'bioequivalente_meta_box_callback',
        $screen
    );

    // Principio activo
    add_meta_box(
        'productos-principio_activo',
        'Principio activo',
        'principio_activo_meta_box_callback',
        $screen
    );

    // Forma farmacéutica
    add_meta_box(
        'productos-forma_farmaceutica',
        'Forma farmacéutica',
        'forma_farmaceutica_meta_box_callback',
        $screen
    );

    // Control de Stock
    add_meta_box(
        'productos-control_de_stock',
        'Control de Stock',
        'control_de_stock_meta_box_callback',
        $screen
    );

    // Otros medicamentos
    add_meta_box(
        'productos-otros_medicamentos',
        'Otros medicamentos',
        'otros_medicamentos_meta_box_callback',
        $screen
    );

    // Dosis
    add_meta_box(
        'productos-dosis',
        'Dosis',
        'dosis_meta_box_callback',
        $screen
    );

    // Codigo ISP
    add_meta_box(
        'productos-codigo_isp',
        'Codigo ISP',
        'codigo_isp_meta_box_callback',
        $screen
    );

    // Es medicamento
    add_meta_box(
        'productos-es_medicamento',
        'Es medicamento',
        'es_medicamento_meta_box_callback',
        $screen
    );

    // Mostrar descripción
    add_meta_box(
        'productos-mostrar_descripcion',
        'Mostrar descripción',
        'mostrar_descripcion_meta_box_callback',
        $screen
    );

    // Precio por fracción
    add_meta_box(
        'productos-precio_por_fraccion',
        'Precio por fracción',
        'precio_por_fraccion_meta_box_callback',
        $screen
    );

    // Precio x 100
    add_meta_box(
        'productos-precio_por_100',
        'Precio por 100 ml o 100 G',
        'precio_por_100_meta_box_callback',
        $screen
    );

    // Requiere receta
    add_meta_box(
        'productos-requiere_receta',
        'Requiere receta',
        'requiere_receta_meta_box_callback',
        $screen
    );
}

add_action( 'add_meta_boxes', 'productos_meta_box' );

function laboratorio_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_laboratorio', true);

    echo '<textarea style="width:100%" id="laboratorio" name="laboratorio">' . esc_attr( $value ) . '</textarea>';
}

function enfermedades_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_enfermedades', true);

    echo '<textarea style="width:100%" id="enfermedades" name="enfermedades">' . esc_attr( $value ) . '</textarea>';
}

function bioequivalente_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_bioequivalente', true);

    echo '<textarea style="width:100%" id="bioequivalente" name="bioequivalente">' . esc_attr( $value ) . '</textarea>';
}

function principio_activo_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_principio_activo', true);

    echo '<textarea style="width:100%" id="principio_activo" name="principio_activo">' . esc_attr( $value ) . '</textarea>';
}

function forma_farmaceutica_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_forma_farmaceutica', true);

    echo '<textarea style="width:100%" id="forma_farmaceutica" name="forma_farmaceutica">' . esc_attr( $value ) . '</textarea>';
}

function control_de_stock_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_control_de_stock', true);

    echo '<textarea style="width:100%" id="control_de_stock" name="control_de_stock">' . esc_attr( $value ) . '</textarea>';
}

function otros_medicamentos_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_otros_medicamentos', true);

    echo '<textarea style="width:100%" id="otros_medicamentos" name="otros_medicamentos">' . esc_attr( $value ) . '</textarea>';
}

function dosis_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_dosis', true);

    echo '<textarea style="width:100%" id="dosis" name="dosis">' . esc_attr( $value ) . '</textarea>';
}

function codigo_isp_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_codigo_isp', true);

    echo '<textarea style="width:100%" id="codigo_isp" name="codigo_isp">' . esc_attr( $value ) . '</textarea>';
}

function es_medicamento_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_es_medicamento', true);

    echo '<textarea style="width:100%" id="es_medicamento" name="es_medicamento">' . esc_attr( $value ) . '</textarea>';
}

function mostrar_descripcion_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_mostrar_descripcion', true);

    echo '<textarea style="width:100%" id="mostrar_descripcion" name="mostrar_descripcion">' . esc_attr( $value ) . '</textarea>';
}

function precio_por_fraccion_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_precio_por_fraccion', true);

    echo '<textarea style="width:100%" id="precio_por_fraccion" name="precio_por_fraccion">' . esc_attr( $value ) . '</textarea>';
}

function precio_por_100_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_precio_por_100', true);

    echo '<textarea style="width:100%" id="precio_por_100" name="precio_por_100">' . esc_attr( $value ) . '</textarea>';
}

function requiere_receta_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'productos_nonce', 'productos_nonce' );
    
    $value = get_post_meta($post->ID, '_requiere_receta', true);

    echo '<textarea style="width:100%" id="requiere_receta" name="requiere_receta">' . esc_attr( $value ) . '</textarea>';
}

// ...


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function save_productos_meta_box_data( $post_id ) {
    // O uso global o creo una clase
    global $meta_atts;

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

    foreach ($meta_atts as $meta_att){
        if (isset( $_POST[$meta_att])) {
            $data = sanitize_text_field( $_POST[$meta_att] );
            update_post_meta( $post_id, "_{$meta_att}", $data ); 
        }
    }    
}

add_action( 'save_post', 'save_productos_meta_box_data' );



