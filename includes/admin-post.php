<?php

add_filter('bulk_actions-edit-post', 'wtc_register_translate_actions');

function wtc_register_translate_actions($bulk_actions)
{
    $bulk_actions['translate'] = __('Translate to', 'wtc');

    //add_thickbox();

    return $bulk_actions;
}

//add_filter('handle_bulk_actions-edit-post', 'wtc_translate_action_handler', 10, 3);

function wtc_translate_action_handler($redirect_to, $doaction, $post_ids)
{
    if ($doaction !== 'translate') {
        return $redirect_to;
    }

    $languages = $_GET['languages'];

    foreach ($post_ids as $post_id) {
        // Perform action for each post.
    }

    return add_query_arg('bulk_emailed_posts', count($post_ids), $redirect_to);
}

