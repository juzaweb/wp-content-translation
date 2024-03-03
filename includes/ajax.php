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

        $wpdb->query('COMMIT');
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        throw $e;
    }

    wp_send_json($result);

    wp_die();
}

add_action('wp_ajax_receive_post', 'wtc_ajax_receive_post');
function wtc_ajax_receive_post()
{
    global $wpdb;

    $options = get_option('wtc_options');

    // check header authorization
    if (!isset($_SERVER['Authorization']) || $_SERVER['Authorization'] !== 'Basic '.get_option('wtc_auto_post_key')) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized.']);

        wp_die();
    }

    $title = sanitize_text_field($_POST['title']);
    $content = esc_html($_POST['content']);
    $source_content_id = $_POST['source_content_id'];
    $locale = $_POST['locale'];

    $translate_log = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wtc_translate_histories WHERE remote_content_id = %s AND locale = %s;",
            $source_content_id,
            $locale
        )
    );

    if (empty($translate_log)) {
        wp_send_json(['success' => false, 'message' => 'Not found.']);

        wp_die();
    }

    if ($options['wtc_api_new_post'] == 'new-post') {
        $oldpost = get_post($translate_log->post_id);
        $post = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => $oldpost->post_type,
            'post_author' => $oldpost->post_author,
        );

        $new_post_id = wp_insert_post($post);
        // Copy post metadata
        $data = get_post_custom($translate_log->post_id);
        foreach ($data as $key => $values) {
            foreach ($values as $value) {
                add_post_meta($new_post_id, $key, $value);
            }
        }

        add_post_meta($new_post_id, 'wtc_locale', $locale);

        wp_send_json(['id' => $new_post_id, 'success' => true]);
    }

    if ($options['wtc_api_new_post'] == 'wp-multilang') {
        $oldpost = get_post($translate_log->post_id);

        $post = array(
            'ID' => $oldpost->ID,
            'post_title' => wpm_set_language_value(wpm_value_to_ml_array( $oldpost->post_title ), $title, array(), $locale),
            'post_content' => wpm_set_language_value(wpm_value_to_ml_array( $oldpost->post_content ), $content, array(), $locale),
        );

        wp_update_post($post);

        wp_send_json(['id' => $oldpost->ID, 'success' => true]);
    }

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
            //'new_post_id' => $post->ID,
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

    $result = $api->translate($post_content->remote_content_id, $to_locale);

    if (!isset($result['data']['success'])) {
        $result['message'] = $result['message'] ?? __('Something went wrong. Please try again.', 'wtc');

        $wpdb->update("{$wpdb->prefix}wtc_translate_histories",
            [
                'status' => 'error',
                'error' => $result['message'],
            ],
            [
                'id' => $translate_log->id,
            ]
        );
    }

    return $result;
}
