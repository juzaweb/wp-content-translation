<?php

add_action('wp_ajax_post_translate', 'wtc_ajax_post_translate_handler');

function wtc_ajax_post_translate_handler()
{
    global $wpdb;

    $post_id = $_POST['post_id'];
    $to_locale = $_POST['to_locale'];
    $default_locale = wtc_get_default_language();

    $post = get_post($post_id);

    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wtc_post_content WHERE post_id = %s;", $post->ID);
    $row = $wpdb->get_row( $query );

    if (function_exists('wpm_translate_object') && wtc_is_wp_multilang_support()) {
        $post = wpm_translate_object($post, $default_locale);
    }

    $api = new MyCrawlersAPI();
    if (empty($row)) {
        $remote_post = $api->postContent(
            $post->post_title,
            $post->post_content,
            $default_locale,
        );

        $row = $wpdb->insert("{$wpdb->prefix}wtc_post_content", [
            'post_id' => $post->ID,
            'remote_content_id' => $remote_post['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    wp_send_json($api->translate($row->remote_content_id, $to_locale));

    wp_die();
}
