<?php
/**
 * Plugin Name: Content Translation
 * Description: Translate content for your WordPress site.
 * Version: 1.0
 * Author: My Crawlers
 * Author URI: https://mycrawlers.com
 * Text Domain: wtc
 * Requires at least: 6.3
 * Requires PHP: 7.4
 */

define('WTC_BASE_PATH', __DIR__);

include __DIR__ . '/components/styles.php';
include __DIR__ . '/components/helpers.php';
include __DIR__ . '/components/setting.php';
include __DIR__ . '/components/admin-post.php';
