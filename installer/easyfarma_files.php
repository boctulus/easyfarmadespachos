<?php

# INSTALLER

global $wpdb;

$table_name      = "easyfarma_files";
$table_version   = '1.0.0';
$charset_collate = $wpdb->get_charset_collate();


$table_name = $wpdb->prefix . $table_name;

if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {

    $sql = "CREATE TABLE `$table_name` ( 
        `id` int NOT NULL AUTO_INCREMENT,
        `filename` varchar(250) NOT NULL,
        `filename_as_stored` varchar(60) NOT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)) ENGINE = InnoDB;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $ok = dbDelta($sql);

    if (!$ok){
        return;
    }

    add_option($table_name, $table_version);
}
