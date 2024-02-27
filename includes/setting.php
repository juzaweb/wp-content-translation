<?php

require __DIR__ . '/../libs/MyCrawlersAPI.php';

/**
 * custom option and settings
 */
function wtc_settings_init() {
    register_setting( 'wtc', 'wtc_options' );

    add_settings_section(
        'wtc_section_developers',
        __( 'Login to connect API', 'wtc' ),
        'wtc_section_developers_callback',
        'wtc'
    );

    add_settings_field(
        'wtc_field_email',
        __( 'Email', 'wtc' ),
        'wtc_field_email',
        'wtc',
        'wtc_section_developers',
        array(
            'label_for'         => 'wtc_api_email',
            'class'             => 'wtc_row',
            'wtc_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'wtc_field_pass',
        __( 'Password', 'wtc' ),
        'wtc_field_pass',
        'wtc',
        'wtc_section_developers',
        array(
            'label_for'         => 'wtc_api_pass',
            'class'             => 'wtc_row',
            'wtc_custom_data' => 'custom',
        )
    );
}

add_action( 'admin_init', 'wtc_settings_init' );


function wtc_section_developers_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php esc_html_e( 'Don\'t have an account?', 'wtc' ); ?>
        <a href="https://mycrawlers.com/register" target="_blank">
            <?php esc_html_e( 'Register now', 'wtc' ); ?>
        </a>
    </p>
    <?php
}

function wtc_field_email( $args ) {
    // Get the value of the setting we've registered with register_setting()
    // $options = get_option( 'wtc_options' );
    ?>

    <input type="email"
        id="<?php echo esc_attr( $args['label_for'] ); ?>"
        data-custom="<?php echo esc_attr( $args['wtc_custom_data'] ); ?>"
        name="wtc_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
        required
    >
    <?php
}

function wtc_field_pass( $args ) {
    // Get the value of the setting we've registered with register_setting()
    // $options = get_option( 'wtc_options' );
    ?>

    <input type="password"
           id="<?php echo esc_attr( $args['label_for'] ); ?>"
           data-custom="<?php echo esc_attr( $args['wtc_custom_data'] ); ?>"
           name="wtc_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
           required
    >

    <?php
}

function wtc_options_page() {
    add_menu_page(
        'Content Translation',
        'Content Translation',
        'manage_options',
        'wtc',
        'wtc_options_page_html'
    );
}
add_action( 'admin_menu', 'wtc_options_page' );

function wtc_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // check if the user have submitted the settings
    if ( isset( $_GET['settings-updated'] ) ) {
        $options = get_option( 'wtc_options' );
        $api = new MyCrawlersAPI();
        $response = $api->login($options['wtc_api_email'], $options['wtc_api_pass']);

        if (isset($response['data']['access_token'])) {
            update_option('wtc_api_token', json_encode($response['data']));

            add_settings_error( 'wtc_messages', 'wtc_message', __( 'Connect API successfully', 'wtc' ), 'updated' );
        } else {
            add_settings_error(
                    'wtc_messages',
                    'wtc_message',
                    __( 'Connect API failed:', 'wtc' ) .' '. $response['message'],
            );
        }
    }

    // show error/update messages
    settings_errors( 'wtc_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <?php if (get_option('wtc_api_token')): ?>
            <p><b><?php _e('Connect API successfully', 'wtc'); ?></b></p>
        <?php else: ?>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'wtc' );

                do_settings_sections( 'wtc' );
                // output save settings button
                submit_button( 'Connect API' );
                ?>
            </form>
        <?php endif; ?>
    </div>
    <?php
}