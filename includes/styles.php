<?php

function wtc_selectively_enqueue_admin_script( $hook ) {
    //Add the Select2 CSS file
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');

    //Add the Select2 JavaScript file
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', 'jquery', '4.1.0-rc.0', ['in_footer' => false]);

    wp_enqueue_script( 'wtc_custom_script', plugins_url( 'assets/js/mycralwers.js', __DIR__ ), array('jquery'), '1.0', ['in_footer' => false] );
}

add_action( 'admin_enqueue_scripts', 'wtc_selectively_enqueue_admin_script' );
