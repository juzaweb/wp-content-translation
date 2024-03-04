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
    // if (function_exists('wpm_get_default_language') && wtc_is_wp_multilang_support()) {
    //     return wpm_get_default_language();
    // }

    if (wtc_is_polylang_support()) {
        return pll_default_language();
    }

    return get_locale();
}

function wtc_is_wp_multilang_support()
{
    return is_plugin_active('wp-multilang/wp-multilang.php');
}

function wtc_is_polylang_support()
{
    return is_plugin_active('polylang/polylang.php');
}

function wtc_random_str($length)
{
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}
