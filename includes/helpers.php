<?php

if (!function_exists('dd')) {
    function dd(...$data)
    {
        echo '<pre>';
        var_dump(...$data);
        echo '</pre>';
        die();
    }
}

function wtc_get_default_language()
{
    if (function_exists('wpm_get_default_language') && wtc_is_wp_multilang_support()) {
        return wpm_get_default_language();
    }

    return get_locale();
}

function wtc_is_wp_multilang_support()
{
    return is_plugin_active('wp-multilang/wp-multilang.php');
}
