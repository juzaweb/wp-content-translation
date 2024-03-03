<?php

require __DIR__.'/../libs/MyCrawlersAPI.php';

/**
 * custom option and settings
 */
function wtc_settings_init()
{
    register_setting('wtc', 'wtc_options');

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
        if (wtc_is_wp_multilang_support()) :
            ?>
            <option value="wp-multilang" <?php selected($value,
                'wp-multilang'); ?>><?php esc_html_e('Add language post (WP-Multilang)', 'wtc'); ?></option>
        <?php endif; ?>
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
    if (isset($_GET['settings-updated'])) {
        $api = new MyCrawlersAPI();
        $response = $api->profile();

        if (isset($response['data']['name'])) {
            $options = get_option('wtc_options');

            if (!isset($options['wtc_auto_post'])) {
                $apiKey = wtc_random_str(32);

                $response = $api->postAutoPost([
                    'configs' => [
                        'endpoint' => $options['wtc_api_url'],
                        'api_key' => $apiKey,
                    ],
                ]);

                $options['wtc_auto_post_id'] = $response['data']['id'];
                $options['wtc_auto_post_key'] = $apiKey;

                update_option('wtc_options', $options);
            }

            add_settings_error('wtc_messages', 'wtc_message', __('Update config successfully', 'wtc'), 'updated');
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