<?php

add_filter('bulk_actions-edit-post', 'register_translate_actions');

function register_translate_actions($bulk_actions)
{
    $bulk_actions['translate'] = __('Translate to', 'email_to_eric');

    add_thickbox();

    return $bulk_actions;
}

add_filter('handle_bulk_actions-edit-post', 'translate_action_handler', 10, 3);

function translate_action_handler($redirect_to, $doaction, $post_ids)
{
    if ($doaction !== 'email_to_eric') {
        return $redirect_to;
    }

    foreach ($post_ids as $post_id) {
        // Perform action for each post.
    }

    return add_query_arg('bulk_emailed_posts', count($post_ids), $redirect_to);
}

