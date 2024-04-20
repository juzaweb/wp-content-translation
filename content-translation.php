<?php
/**
 * Plugin Name: Content Translation
 * Description: Automatic translate content for your WordPress site.
 * Version: 1.0
 * Author: My Crawlers
 * Author URI: https://juzaweb.com
 * Text Domain: wtc
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * License: GPLv3 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('WTC_BASE_PATH', __DIR__);

include __DIR__ . '/includes/styles.php';
include __DIR__ . '/includes/ajax.php';
include __DIR__ . '/includes/helpers.php';
include __DIR__ . '/includes/setting.php';
include __DIR__ . '/includes/admin-post.php';

function wtc_create_database_table()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sqls[] = "CREATE TABLE {$wpdb->prefix}wtc_post_content (
        id bigint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) UNSIGNED NOT NULL,
        remote_content_id bigint(20) NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE {$wpdb->prefix}wtc_translate_histories (
        id bigint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) UNSIGNED NOT NULL,
        new_post_id bigint(20) UNSIGNED NULL,
        remote_content_id bigint(20) NULL,
        locale varchar(10) NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        status varchar(10) NOT NULL DEFAULT 'pending',
        error text NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
        FOREIGN KEY (new_post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once ABSPATH.'wp-admin/includes/upgrade.php';

    dbDelta($sqls);
}

register_activation_hook(__FILE__, 'wtc_create_database_table');
