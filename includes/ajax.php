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
    $post_content = $wpdb->get_row($query);

    if (function_exists('wpm_translate_object') && wtc_is_wp_multilang_support()) {
        $post = wpm_translate_object($post, $default_locale);
    }

    $wpdb->query('START TRANSACTION');
    try {
        $result = wtc_post_and_translate($post_content, $post, $to_locale, $default_locale);

        if (!isset($result['data']['success'])) {
            $result['message'] = $result['message'] ?? __('Something went wrong. Please try again.', 'wtc');
            $wpdb->query('ROLLBACK');
        } else {
            $wpdb->query('COMMIT');
        }
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        throw $e;
    }

    wp_send_json($result);

    wp_die();
}

function wtc_post_and_translate($post_content, $post, $to_locale, $default_locale)
{
    global $wpdb;
    $api = new MyCrawlersAPI();
    if (empty($post_content)) {
        $remote_post = $api->postContent(
            $post->post_title,
            $post->post_content,
            $default_locale,
        );

        $wpdb->insert("{$wpdb->prefix}wtc_post_content", [
            'post_id' => $post->ID,
            'remote_content_id' => $remote_post['data']['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wtc_post_content WHERE post_id = %s;", $post->ID);
        $post_content = $wpdb->get_row($query);
    }

    $translate_log = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wtc_translate_histories WHERE post_id = %s AND locale = %s;",
            $post->ID,
            $to_locale
        )
    );

    if ($translate_log && $translate_log->status != 'error') {
        return [];
    }

    if (empty($translate_log)) {
        $wpdb->insert("{$wpdb->prefix}wtc_translate_histories", [
            'post_id' => $post->ID,
            'new_post_id' => $post->ID,
            'remote_content_id' => $post_content->remote_content_id,
            'locale' => $to_locale,
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'pending',
        ]);
    } else {
        $wpdb->update("{$wpdb->prefix}wtc_translate_histories",
            [
                'status' => 'pending',
            ],
            [
                'id' => $translate_log->id,
            ]
        );
    }

    return $api->translate($post_content->remote_content_id, $to_locale);
}
