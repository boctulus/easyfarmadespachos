<?php

use boctulus\EasyFarmaDespachos\libs\Url;
use boctulus\EasyFarmaDespachos\libs\Files;
use boctulus\EasyFarmaDespachos\libs\Strings;
use boctulus\EasyFarmaDespachos\libs\Debug;
use boctulus\EasyFarmaDespachos\libs\Arrays;
use boctulus\EasyFarmaDespachos\libs\Orders;
use boctulus\EasyFarmaDespachos\libs\MultipleUploader;

require_once __DIR__ . '/libs/Url.php';
require_once __DIR__ . '/libs/Files.php';
require_once __DIR__ . '/libs/Strings.php';
require_once __DIR__ . '/libs/Debug.php';
require_once __DIR__ . '/libs/Arrays.php';
require_once __DIR__ . '/libs/Orders.php';
require_once __DIR__ . '/libs/MultipleUploader.php';

/*
    Recibo un token y cambio la contraseña
*/
function get_order(WP_REST_Request $req){
    global $config;

    $error = new WP_Error();
    
    try {
        $order_id = $req->get_param('id');
        $order    = Orders::getOrderById($order_id); 

        //var_dump($order);

        if (!$order){
            throw new \InvalidArgumentException("Order ID no encontrada");
        }

        $data     = Orders::getOrderData($order);   
        $customer = Orders::getCustomerData($order);
        $items    = Orders::getOrderItems($order);

        $products = [];
        foreach ($items as $item){
            $products[] = Orders::orderItemToArray($item);
        }

        $data['customer'] = $customer;
        $data['products'] = $products;

        $res = new WP_REST_Response($data);
        $res->set_status(200);

        return $res;
        
    } catch (\Exception $e){    
        $error->add(500, $e->getMessage());
        return $error;
    }
}
function post_ficha_despacho(WP_REST_Request $req){
    global $config;

    $data = $req->get_body();

    try {
        if ($data === null) {
            throw new \Exception("No se recibió la data");
        }

        $data = Url::bodyDecode($data);

        //$error = new WP_Error();

        Files::dump($data);

        $res = [
            'success' => true
        ];

        $res = new WP_REST_Response($res);
        $res->set_status(200);

        return $res;

    } catch (\Exception $e){
        Files::logger($e->getMessage());
    }
}

/*
    endpoint para subir archivos

    => convertir a WP
*/
function file_upload(){
    global $wpdb;

    $data     = $_POST;
    $order_id = $data['order_id'] ?? '';

    $error = new WP_Error();

    if (empty($order_id)){
        $error->add(400, 'El parametro order_id es requerido');
        return $error;
    }

    $kv = [];
    foreach($data as $key => $val){
        $kv[] = "$key=$val"; 
    }

    $prefix = implode('-', $kv);

    $uploader = new MultipleUploader();

    $uploader
    ->setLocation(__DIR__ . '/../../uploads/easyfarmadespachos')
    ->setFileHandler(function($prefix) {

        return $prefix .'-'. time();
     
    }, $prefix);


    $files    = $uploader->doUpload()->getFileNames();   
    $failures = $uploader->getErrors();     

    if (count($files) == 0){
        $error->add(400, 'No files or file upload failed');
        return $error;
    }        

    /*
        Almaceno los nombres de los archivos en DB
    */
    foreach($files as $ix => $f){
        $ori_filename = $f['ori_name'];
        $as_stored    = $f['as_stored'];

        $sql = "INSERT INTO `{$wpdb->prefix}easyfarma_files` (`id`, `filename`, `filename_as_stored`, `created_at`) VALUES (NULL, '$ori_filename', '$as_stored', CURRENT_TIMESTAMP);";

        $wpdb->query($sql);
        $id = $wpdb->insert_id;

        $files[$ix]['id'] = $id;
    }
    
    return [
        'data'     => $data,
        'files'    => $files,
        'failures' => $failures,
        'message'  => !empty($failures) ? 'Got errors during file upload' : null
    ];
}

add_action('rest_api_init', function () {
    # GET /wp-json/orders/v1/get

    register_rest_route('orders/v1', '/get/(?P<id>[0-9]+)', array(
        'methods' => 'GET',
        'callback' => 'get_order',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('despachos/v1', '/post', array(
        'methods' => 'POST',
        'callback' => 'post_ficha_despacho',
        'permission_callback' => '__return_true'
    ));

    // /wp-json/ez_files/v1/post
    register_rest_route('ez_files/v1', '/post', array(
        'methods' => 'POST',
        'callback' => 'file_upload',
        'permission_callback' => '__return_true'
    ));
});
