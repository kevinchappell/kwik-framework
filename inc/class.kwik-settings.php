<?php

class KwikSettings {

	public function __construct(){

	}

	/**
	 * Starts building the settings page and section for plugin or theme
	 * @param  [String] $name     'my-plugin'
	 * @param  [String] $page     'my-plugin-settings'
	 * @param  [Array]  $settings default settings array
	 */
	public function settings_init( $name, $page, $settings ) {
	    $validate = new KwikValidate( $settings );
	    wp_enqueue_script( 'jquery-ui-tabs' );
	    $options = get_option($page);
	    foreach ( $settings as $section => $val ) {
	        register_setting( $page, $page, array($validate, 'validate_settings' ) );
	        add_settings_section(
	            $section, // section id
	            $val['section_title'],
	            $val['section_desc'], // callback for section
	            $page
	        );
	        $this->add_kf_fields( $val['settings'], $section, $page, $settings );
	    }
	}

	 /**
	 * Registers fields to sections of your settings page
	 * @param [Array]  $fields   array of fields for current section
	 * @param [String] $section  section fields are being added to
	 * @param [String] $page     setting namespace the field is being added to
	 * @param [Array]  $settings default settings array to iterate through
	 */
	private function add_kf_fields( $fields, $section, $page, $settings ) {
	    foreach ($fields as $k => $v) {
	        $current_field = $settings[$section]['settings'][$k];
	        $formatted_field = $this->format_fields_vars( $current_field, $v );
	        add_settings_field(
	            $k, // id
	            $formatted_field['title'], // title
	            $formatted_field['callback'], //callback, type or multi to insert multiple fields in single settings
	            $page,
	            $section, // section
	            $formatted_field['args']
	        );
	        $current_field['desc'] = '';
	    }
	}


	/**
	 * Formats the properties of a field array
	 * @param  [array]   $current_field the current field in our iteration
	 * @param  [dynamic] $val           value of the current field
	 * @return [array]               formatted field array
	 * @todo cleanup
	 */
	private function format_fields_vars( $current_field, $val ){
	    $desc = isset($current_field['desc']) ? $current_field['desc'] : null;
	    if (!isset($val['type']) || (isset($val['type']) && $val['type'] === 'multi')) {
	        $val['type'] = 'multi';
	        $args = array(
	            'desc' => $desc,
	            'attrs' => array('fields' => $current_field['fields']),
	            'options' => null,
	        );
	        $callback = 'multi';
	    } else {
	        $args = array(
	            'value' => isset($current_field['value']) ? $current_field['value'] : null,
	            'desc' => $desc,
	            'attrs' => isset($current_field['attrs']) ? $current_field['attrs'] : null,
	            'options' => isset($current_field['options']) ? $current_field['options'] : null
	        );

	        $callback = $val['type'];
	    }
	    return array(
	        'type'      => isset( $val['type'] ) ? $val['type'] : '',
	        'title'     => isset( $val['title'] ) ? $val['title'] : '',
	        'args'      => $args,
	        'callback'  => $callback
	        );
	}


	public static function settings_sections($page, $settings)
	{
	    $inputs = new KwikInputs();
	    global $wp_settings_sections;

	    if (!isset($wp_settings_sections) || !isset($wp_settings_sections[$page])) {
	        return;
	    }
	    $settings_sections = $wp_settings_sections[$page];

	    $output = self::build_section_nav($settings_sections);
	    $output .= self::build_sections($settings_sections, $page, $settings);

	    return $inputs->markup('div', $output, array('class' => KF_PREFIX . 'settings'));
	}

	private static function build_section_nav($sections)
	{
	    $section_nav = '';
	    $inputs = new KwikInputs();
	    foreach ((array) $sections as $section) {
	        $nav_link = $inputs->markup('a', $section['title'], array('href' => '#' . KF_PREFIX . $section['id']));
	        $section_nav .= $inputs->markup('li', $nav_link);
	    }
	    $section_nav .= $inputs->markup('li', get_submit_button(__('Save', 'kwik')), array("class" => 'kf_submit'));
	    return $inputs->markup('ul', $section_nav, array('class' => KF_PREFIX . 'settings_index'));
	}


	/**
	 * build markup for each section of our settings page
	 * @param  [Array] $settings_sections    array of sections containing title, description
	 * @param  [String] $page
	 * @param  [Array] $settings
	 * @return [String]                      markup for each section and its fields
	 */
	private static function build_sections($settings_sections, $page, $settings)
	{
	    global $wp_settings_fields;
	    $inputs = new KwikInputs();
	    $sections = '';
	    foreach ((array) $settings_sections as $section) {
	        $cur_section = !empty($section['title']) ? $inputs->markup('h3', $section['title']) : "";
	        $cur_section .= $inputs->markup('p', $section['callback']);
	        if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
	            continue;
	        }
	        $settings_fields = self::settings_fields($page, $section['id'], $settings);
	        $cur_section .= $inputs->markup('table', $settings_fields, array('class' => 'form-table'));
	        $sections .= $inputs->markup('div', $cur_section, array('class' => KF_PREFIX . 'options_panel', 'id' => KF_PREFIX . $section['id']));
	    }
	    return $sections;
	}

	private static function settings_fields($page, $section, $settings)
	{
	    $inputs = new KwikInputs();
	    $output = '';

	    global $wp_settings_fields;
	    if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section])) {
	        return;
	    }

	    $section_fields = (array) $wp_settings_fields[$page][$section];

	    foreach ($section_fields as $field) {
	        $setting_meta = self::setting_field_meta($field, $inputs);

	        if (!empty($field['args']['label_for'])) {
	            $field['title'] = $inputs->markup('label', $setting_meta['title'], array('for' => $field['args']['label_for']));
	        }

	        $th_tag = $inputs->markup('th', $setting_meta['title'], array('scope' => 'row'));
	        $val = isset($field['args']['value']) ? $field['args']['value'] : '';
	        $value = isset($settings[$setting_meta['id']]) ? $settings[$setting_meta['id']] : $val;

	        $field = $inputs->$field['callback'](
	            $page . '[' . $setting_meta['id'] . ']', // name
	            $value, // value
	            null, // label
	            $field['args']['attrs'],
	            $field['args']['options']// options
	        );

	        $td_tag = $inputs->markup('td', $field);
	        $output .= $inputs->markup('tr', $th_tag . $td_tag, array('valign' => 'top', 'class' => array($setting_meta['id'], KF_PREFIX . 'option', 'type-' . $setting_meta['type'], $setting_meta['error_class'])));

	    }
	    return $output;
	}

	private static function setting_field_meta($field, $inputs)
	{
	    $meta = array(
	        'id' => esc_attr($field['id']),
	        'type' => $field['callback'],
	        'title' => esc_attr($field['title']),
	        'error_class'=> ''
	        );
	    $setting_error = get_settings_errors($meta['id']);
	    if (isset($setting_error[0])) {
	        $meta['title'] = $meta['title'] . $inputs->markup('span', '!', array('class' => 'error_icon', 'tooltip' => $setting_error[0]['message']));
	        $meta['error_class'] = 'error';
	    }
	    if ($field['args']['desc']) {
	        $desc = $inputs->markup('span', '', array('class' => 'dashicons ks_info_tip', 'tooltip' => $field['args']['desc']));
	        $meta['title'] .= ' ' . $desc;
	    }
	    return $meta;
	}

}
