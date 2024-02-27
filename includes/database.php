<?php

function wtc_create_database_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wtc_translate_histories';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$wpdb->prefix}wtc_post_content (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        remote_content_id mediumint(9) NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $sql .= "CREATE TABLE {$wpdb->prefix}wtc_translate_histories (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        remote_content_id mediumint(9) NOT NULL,
        locale varchar(10) NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        status varchar(10) NOT NULL DEFAULT 'pending',
        error text NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

register_activation_hook( __FILE__, 'wtc_create_database_table' );
