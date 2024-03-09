<?php

require __DIR__.'/../libs/MyCrawlersAPI.php';

function wtc_settings_init()
{
    register_setting('wtc', 'wtc_options');
    register_setting('wtc_auto_post', 'wtc_auto_post_id');
    register_setting('wtc_auto_post', 'wtc_auto_post_key');

    add_settings_section(
        'wtc_section_developers',
        __('Login to connect API', 'wtc'),
        'wtc_section_developers_callback',
        'wtc'
    );

    add_settings_field(
        'wtc_field_api_key',
        __('API Key', 'wtc'),
        'wtc_field_api_key',
        'wtc',
        'wtc_section_developers',
        array(
            'label_for' => 'wtc_api_key',
            'class' => 'wtc_row',
            'wtc_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'wtc_field_new_post',
        __('What to do when there is a new translation?', 'wtc'),
        'wtc_field_new_post',
        'wtc',
        'wtc_section_developers',
        array(
            'label_for' => 'wtc_api_new_post',
            'class' => 'wtc_row',
            'wtc_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'wtc_field_new_post_status',
        __('What to do when there is a new translation?', 'wtc'),
        'wtc_field_new_post_status',
        'wtc',
        'wtc_section_developers',
        array(
            'label_for' => 'wtc_api_new_post_status',
            'class' => 'wtc_row',
            'wtc_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'wtc_field_api_url',
        __('Base URL', 'wtc'),
        'wtc_field_api_url',
        'wtc',
        'wtc_section_developers',
        array(
            'label_for' => 'wtc_api_url',
            'class' => 'wtc_row',
            'wtc_custom_data' => 'custom',
        )
    );
}

add_action('admin_init', 'wtc_settings_init');


function wtc_section_developers_callback($args)
{
    ?>
    <p id="<?php echo esc_attr($args['id']); ?>">
        <?php esc_html_e('Don\'t have an account?', 'wtc'); ?>
        <a href="https://mycrawlers.com/register" target="_blank">
            <?php esc_html_e('Register now', 'wtc'); ?>
        </a>
    </p>
    <?php
}

function wtc_field_api_key($args)
{
    // Get the value of the setting we've registered with register_setting()
    $options = get_option('wtc_options');
    ?>

    <input type="text"
           id="<?php echo esc_attr($args['label_for']); ?>"
           data-custom="<?php echo esc_attr($args['wtc_custom_data']); ?>"
           name="wtc_options[<?php echo esc_attr($args['label_for']); ?>]"
           value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>"
           required
    >
    <?php
}

function wtc_field_new_post($args)
{
    // Get the value of the setting we've registered with register_setting()
    $options = get_option('wtc_options');
    $value = $options[$args['label_for']] ?? '';
    ?>

    <select
            id="<?php echo esc_attr($args['label_for']); ?>"
            data-custom="<?php echo esc_attr($args['wtc_custom_data']); ?>"
            name="wtc_options[<?php echo esc_attr($args['label_for']); ?>]"
            required
    >
        <option value="new-post" <?php selected($value, 'new-post'); ?>><?php esc_html_e('Add New Post',
                'wtc'); ?></option>

        <?php
        if (wtc_is_polylang_support()) :
            ?>
            <option value="polylang" <?php selected($value,
                'polylang'); ?>><?php esc_html_e('Add language post (Polylang)', 'wtc'); ?></option>
        <?php endif; ?>
    </select>

    <?php
}

function wtc_field_new_post_status($args)
{
    // Get the value of the setting we've registered with register_setting()
    $options = get_option('wtc_options');
    $value = $options[$args['label_for']] ?? 'draft';
    $post_statuses = get_post_statuses();
    ?>

    <select
            id="<?php echo esc_attr($args['label_for']); ?>"
            data-custom="<?php echo esc_attr($args['wtc_custom_data']); ?>"
            name="wtc_options[<?php echo esc_attr($args['label_for']); ?>]"
            required
    >
        <?php foreach ($post_statuses as $key => $status) : ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($value, $key); ?>>
                <?php echo esc_html($status); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

function wtc_field_api_url($args)
{
    // Get the value of the setting we've registered with register_setting()
    $options = get_option('wtc_options');
    ?>

    <input type="text"
           id="<?php echo esc_attr($args['label_for']); ?>"
           data-custom="<?php echo esc_attr($args['wtc_custom_data']); ?>"
           name="wtc_options[<?php echo esc_attr($args['label_for']); ?>]"
           value="<?php echo esc_attr($options[$args['label_for']] ?? get_site_url()); ?>"
           required
    >
    <?php
}

function wtc_options_page()
{
    add_menu_page(
        'Content Translation',
        'Content Translation',
        'manage_options',
        'wtc',
        'wtc_options_page_html'
    );
}

add_action('admin_menu', 'wtc_options_page');

function wtc_options_page_html()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // check if the user have submitted the settings
    $nonce = wp_create_nonce('wtc_options');
    if (isset($_GET['settings-updated']) && wp_verify_nonce($nonce, 'wtc_options')) {
        $api = new MyCrawlersAPI();
        $response = $api->profile();

        if (isset($response['data']['name'])) {
            $options = get_option('wtc_options');

            $error = false;
            if (!get_option('wtc_auto_post_id')) {
                $apiKey = wtc_random_str(32);

                $response = $api->postAutoPost([
                    'configs' => [
                        'endpoint' => $options['wtc_api_url'].'/wp-admin/admin-ajax.php?action=receive_post',
                        'api_key' => $apiKey,
                    ],
                ]);

                if (isset($response['data']['id'])) {
                    require_once( ABSPATH . 'wp-load.php' );

                    update_option('wtc_auto_post_id', $response['data']['id']);
                    update_option('wtc_auto_post_key', $apiKey);
                } else {
                    $error = $response['message'] ?? __('Unknown error', 'wtc');
                }
            }

            if ($error) {
                add_settings_error(
                    'wtc_messages',
                    'wtc_message',
                    __('Connect API failed:', 'wtc').' '.$error,
                    'error'
                );
            } else {
                add_settings_error(
                    'wtc_messages',
                    'wtc_message',
                    __('Update config successfully', 'wtc'),
                    'updated'
                );
            }
        } else {
            add_settings_error(
                'wtc_messages',
                'wtc_message',
                __('Connect API failed:', 'wtc').' '.$response['message'],
            );
        }
    }

    // show error/update messages
    settings_errors('wtc_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wtc');

            do_settings_sections('wtc');
            // output save settings button
            submit_button('Update Settings');
            ?>
        </form>
    </div>
    <?php
}