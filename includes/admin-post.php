<?php

// Add the translation action
add_filter('bulk_actions-edit-post', 'wtc_register_translate_action');

function wtc_register_translate_action($bulk_actions)
{
    $bulk_actions['translate'] = __('Translate to', 'wtc');

    return $bulk_actions;
}

// Add the custom columns to the post types
add_filter( 'manage_posts_columns', 'set_custom_edit_book_columns' );
function set_custom_edit_book_columns($columns) {
    unset( $columns['author'] );
    $columns['translate_versions'] = __( 'Translate Status', 'wtc' );

    return $columns;
}

add_action( 'manage_posts_custom_column' , 'custom_book_column', 10, 2 );
function custom_book_column( $column, $post_id ) {
    if ($column == 'translate_versions') {
        global $wpdb;

        $translate_logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wtc_translate_histories WHERE post_id = %s and status in ('pending', 'error');",
                $post_id
            )
        );

        foreach ($translate_logs as $translate_log) {
            if ($translate_log->status == 'pending') {
                echo $translate_log->locale . ': ' . __('Translating ', 'wtc');
            } elseif ($translate_log->status == 'error') {
                echo $translate_log->locale . ': ' . __('Error', 'wtc');
            }
        }
    }
}

// add_filter('handle_bulk_actions-edit-post', 'wtc_translate_action_handler', 10, 3);

// function wtc_translate_action_handler($redirect_to, $doaction, $post_ids)
// {
//     if ($doaction !== 'translate') {
//         return $redirect_to;
//     }
//
//     $languages = $_GET['languages'];
//
//     foreach ($post_ids as $post_id) {
//         // Perform action for each post.
//     }
//
//     return add_query_arg('bulk_emailed_posts', count($post_ids), $redirect_to);
// }
