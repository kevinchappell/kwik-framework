#Kwik Framework

This is a framework for rapid development of WordPress themes and Plugins. It lets you quickly create option pages with dynamic error handling, programmatically generate markup where needed and provides custom inputs for your theme or plugin.

## Usage
Simply installing the plugin provides a robust API that lets you quickly create settings pages for you plugin or theme.

**Generating Setting Page**
```
<?php
add_action( 'admin_menu', 'my_plugin_add_admin_menu' );
add_action( 'admin_init', 'my_plugin_settings_init' );

function my_plugin_add_admin_menu() {
  // add_submenu_page( 'edit.php?post_type=kwik-framework', __('Kwik Framework Settings', 'kwik'), __('Settings', 'kwik'), 'manage_options', 'kwik_framework', 'my-plugin' );
  add_options_page('Kwik Framework Settings', 'Kwik Framework', 'manage_options', 'my-plugin', 'my-plugin'.'_settings');
}

function my_plugin_settings_init() {
  $utils = new KwikUtils();
  $defaultSettings = my_plugin_default_options();
  $utils->settings_init(MyPlugin, 'my-plugin', $defaultSettings);
}

function kwik_framework_settings() {
  $settings = my_plugin_get_options();
  echo '<div class="wrap">';
    echo KwikInputs::markup('h2', __('Framework Settings', 'kwik'));
    echo KwikInputs::markup('p', __('Set the API keys and other options for your website. Kwik Framework needs these settings to connect to Google fonts and other APIs.','kwik'));
    echo '<form action="options.php" method="post">';
      settings_fields('my-plugin');
      echo KwikUtils::settings_sections('my-plugin', $settings);
    echo '</form>';
  echo '</div>';
  echo KwikInputs::markup('div', $output, array('class'=>'wrap'));
}

function my_plugin_get_options() {
  return get_option('my-plugin', my_plugin_default_options());
}

function my_plugin_default_options() {

  $my_plugin_default_options = array(
    'section_1' => array(
      'section_title' => __('Section One', 'kwik'),
      'section_desc' => __('This is the description for section one.', 'kwik'),
      'settings' => array(
        'sec_1_first_option' => array(
          'type' => 'text',
          'title' => __('First Option', 'kwik'),
          'value' => ''
        ),
        'sec_2_second_option' => array(
          'type' => 'text',
          'title' => __('Second Option', 'kwik'),
          'value' => ''
        )
      )
    ),
    'section_2' => array(
      'section_title' => __('Section Two', 'kwik'),
      'section_desc' => __('This is the description for section two.', 'kwik'),
      'settings' => array(
        'sec_2_first_option' => array(
          'type' => 'text',
          'title' => __('Option One Title', 'kwik'),
          'value' => ''
        ),
        'sec_2_second_option' => array(
          'type' => 'text',
          'title' => __('Option Two Title', 'kwik'),
          'value' => ''
        )
      )
    )
  );

  return apply_filters('my_plugin_default_options', $kf_default_options);
}
?>
```
That's it. The above code block will add a new options page to your theme or plugin with automatic field validation. In this example, options are added to the `my_plugin_default_options` multi-dimensional array. Type is defined as the input type to be used such as `text` and `select` but Kwik Framework also provides the following custom types `img`, `font`, `toggle`, `color`, `link`, `spinner` and `nonce`. The custom inputs can be easily extended using the `input` or `multi` types and supplying your own attributes.



**Generating markup**
```
$inputs = new KwikInputs();

$link = $inputs->markup('a', "This is a link", array("class" => "test_link", href="http://test-site.com", "title" => "Test Title"));

echo $link;
```

## Widgets

### Latest Posts ###
Displays a list of posts. Features:
- Filter by category and tag
- date and read more formatting
- numerous options

