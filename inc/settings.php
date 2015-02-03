<?php
add_action('admin_menu', 'kf_add_admin_menu');
add_action('admin_init', 'kf_settings_init');

function kf_add_admin_menu()
{
    add_options_page('Kwik Framework Settings', 'Kwik Framework', 'manage_options', KF_FUNC, KF_FUNC . '_settings');
}

function kf_settings_init()
{
    $kwik_settings = new KwikSettings();
    $defaultSettings = kf_default_options();
    $kwik_settings->settings_init(KF_BASENAME, KF_FUNC, $defaultSettings);
}

function kwik_framework_settings()
{
    $settings = kf_get_options();
    echo '<div class="wrap">';
    echo KwikInputs::markup('h2', __('Framework Settings', 'kwik'));
    echo KwikInputs::markup('p', __('Set the API keys and other options for your website. Kwik Framework needs these settings to connect to Google fonts and other APIs.', 'kwik'));
    echo '<form action="options.php" method="post">';
    settings_fields(KF_FUNC);
    echo KwikSettings::settings_sections(KF_FUNC, $settings);
    echo '</form>';
    echo '</div>';
}

function kf_get_options()
{
    return get_option(KF_FUNC, kf_default_options());
}

function kf_default_options()
{

    $kf_default_options = array(
        'Google' => array(
            'section_title' => __('Google', 'kwik'),
            'section_desc' => __('Enter your API keys to connect to the below services.', 'kwik'),
            'settings' => array(
                'fonts_key' => array(
                    'type' => 'text',
                    'title' => __('Google Fonts', 'kwik'),
                    'value' => '',
                ),
                'analytics_id' => array(
                    'type' => 'text',
                    'title' => __('Google Analtytics Tracking ID', 'kwik'),
                    'value' => '',
                ),
            ),
        ),
    );

    return apply_filters('kf_default_options', $kf_default_options);
}
