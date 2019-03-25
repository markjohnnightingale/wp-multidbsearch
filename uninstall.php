<?php 

if (!WP_UNINSTALL_PLUGIN) {
    die();
}

global $wpdb;
$table_name = $wpdb->prefix . 'other_dbs';
$sql = "DROP TABLE IF EXISTS $table_name;";
$wpdb->query($sql);
