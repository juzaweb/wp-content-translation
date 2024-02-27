<?php
/**
 * Plugin Name: Content Translation
 * Description: Automatic translate content for your WordPress site.
 * Version: 1.0
 * Author: My Crawlers
 * Author URI: https://mycrawlers.com
 * Text Domain: wtc
 * Requires at least: 6.3
 * Requires PHP: 7.4
 */

define('WTC_BASE_PATH', __DIR__);

include __DIR__ . '/includes/database.php';
include __DIR__ . '/includes/styles.php';
include __DIR__ . '/includes/ajax.php';
include __DIR__ . '/includes/helpers.php';
include __DIR__ . '/includes/setting.php';
include __DIR__ . '/includes/admin-post.php';
