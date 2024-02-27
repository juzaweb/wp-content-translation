<?php

add_action( 'wp_ajax_post_translate', 'wtc_ajax_post_translate_handler' );

function wtc_ajax_post_translate_handler() {


    wp_send_json([]);

    wp_die();
}
