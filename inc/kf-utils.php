<?php


/**
 * Description: Collection of utilities for common PHP and WordPress tasks for themes and plugins
 */
Class KwikUtils {

  /* returns a result form url */
  private function curl_get_result($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }

  /**
   * fetch a resource using cURL then cache for next use.
   * @param  [String] $url    - url of the resource to be fetched
   * @param  [String] $type   - type of resource to be fetched (fonts, tweets, etc)
   * @return [JSON]
   */
  private function fetchCachedResource($url, $type, $expire) {
    $cache_file = KF_CACHE . '/' . $type;
    $last = file_exists($cache_file) ? filemtime($cache_file) : false;
    $now = time();

    // check the cache file
    if (!$last || (($now - $last) || !file_exists($cache_file) > $expire)) {

      $cache_rss = $this->curl_get_result($url);

      if ($cache_rss) {
        $cache_static = fopen($cache_file, 'wb');
        fwrite($cache_static, $cache_rss);
        fclose($cache_static);
      }
    }

    return file_get_contents($cache_file);
  }

  public function get_google_fonts($api_key) {

    $feed = "https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&fields=items(category%2Cfamily%2Cvariants)&key=" . $api_key;

    $fonts = json_decode($this->fetchCachedResource($feed, 'fonts', 1200));

    if ($fonts) {           // are there any results?
      return $fonts->items;
    } else {                // There are no fonts... somehow
      return false;
    }
  }

  public function __update_meta($post_id, $field_name, $value = ''){
    if (empty($value) OR !$value) {
      delete_post_meta($post_id, $field_name);
    } elseif (!get_post_meta($post_id, $field_name)) {
      add_post_meta($post_id, $field_name, $value);
    } else {
      update_post_meta($post_id, $field_name, $value);
    }
  }

  public function get_all_post_types() {
    $all_post_types = array();
    $args = array(
      'public' => true,
      '_builtin' => true
    );
    $output = 'objects';// names or objects, note names is the default
    $operator = 'and';// 'and' or 'or'

    $default_post_types = get_post_types($args, $output, $operator);

    foreach ($default_post_types as $k => $v) {
      $all_post_types[$k]['label'] = $v->labels->name;
      $all_post_types[$k]['name'] = $v->name;
    }

    $args = array(
      'public' => true,
      '_builtin' => false
    );

    $custom_post_types = get_post_types($args, $output, $operator);

    foreach ($custom_post_types as $k => $v) {
      $all_post_types[$k]['label'] = $v->labels->name;
      $all_post_types[$k]['name'] = $v->name;
    }

    array_push($all_post_types, array('name' => '404', 'label' => __('404 Not Found', 'kwik')));

    return $all_post_types;
  }

  public function number_to_string($num, $echo = FALSE){
    $numbers = array('zero','one','two','three','four','five','six','seven', 'eight', 'nine', 'ten');
    if($echo){
      echo $numbers[$num];
    } else {
      return $numbers[$num];
    }
  }

  public function number_to_class($num, $echo = FALSE){
    $numbers = array('','one','halves','thirds','fourths','fifths','sixths','sevenths');
    if($echo){
      echo $numbers[$num];
    } else {
      return $numbers[$num];
    }
  }

  public function settings_init($name, $page, $settings) {
    $options = get_option($page);
    foreach ($settings as $section => $val) {
      register_setting($page, $page, $this->settings_validate);
      add_settings_section(
        $section, // section id
        $val['section_title'],
        $val['section_desc'], // callback for section
        $page
        );
      foreach ($val['settings'] as $k => $v) {
        $args = array(
          'value' => $settings[$section]['settings'][$k]['value'],
          'options' => $settings[$section]['settings'][$k]['options'],
          'attrs' => $settings[$section]['settings'][$k]['attrs']
        );
        add_settings_field(
          $k, // id
          $v['title'], // title
          $v['type'], //callback, but we are sending a type to circum `call_user_func`
          $page,
          $section, // section
          $args
          );
      }
    }
  }

  private function section_callback($section){
    $inputs = new KwikInputs();
    return $inputs->markup('p', $section['callback']);
  }


  public function settings_sections($page, $settings){
    $inputs = new KwikInputs();
    global $wp_settings_sections, $wp_settings_fields;

    if (!isset($wp_settings_sections) || !isset($wp_settings_sections[$page])) {
      return;
    }

    $output = '';
    foreach ((array) $wp_settings_sections[$page] as $section) {
      $section_nav_li .= $inputs->markup('li', '<a href="#' .KF_PREFIX. $section['id'] . '">' . $section['title'] . '</a>');
    }
    $save_btn = $inputs->markup('li', get_submit_button(__('Save', 'kwik')), array("class" => 'kf_submit'));
    $output .= $inputs->markup('ul', $section_nav_li.$save_btn, array("class" => KF_PREFIX.'settings_index'));

    foreach ((array) $wp_settings_sections[$page] as $section) {
      $cur_section = !empty($section['title']) ? $inputs->markup('h3', $section['title']) : "";
      $cur_section .= $this->section_callback($section);

      if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
        continue;
      }
      $settings_fields = $this->settings_fields($page, $section['id'], $settings);
      $cur_section .= $inputs->markup('table', $settings_fields, array("class" => "form-table"));
      $output .= $inputs->markup('div', $cur_section, array("class" => KF_PREFIX."options_panel", "id" => KF_PREFIX. $section['id']));

    }

    $output = $inputs->markup('div', $output, array("class" => KF_PREFIX."settings", "id" => KF_PREFIX. $section['id']));

    return $output;
  }


  private function settings_fields($page, $section, $settings) {
    $inputs = new KwikInputs();
    global $wp_settings_fields;
    if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section])) {
      return;
    }

    $sectionFields = (array) $wp_settings_fields[$page][$section];

    foreach ($sectionFields as $field) {

      if (!empty($field['args']['label_for'])) {
        $field['title'] = $inputs->markup('label', $field['title'], array('for'=>$field['args']['label_for']));
      }

      $th = $inputs->markup('th', $field['title'], array('scope'=>'row'));
      $value = $settings[$field['id']] ? $settings[$field['id']] : $field['args']['value'];

      if($field['callback'] === 'select'){
        $field = $inputs->$field['callback'](
          $page.'['.$field['id'].']', // name
          $value, // value
          $field['args']['options'] // options
          );
      } else {
        $field = $inputs->$field['callback'](
          $page.'['.$field['id'].']', // name
          $value, // value
          NULL, // label
          $field['args']['attrs']
          );
      }

      $td = $inputs->markup('td', $field, array('class' => $field['id']));
      $output .= $inputs->markup('tr', $th.$td, array('valign'=>'top'));

    }
      return $output;
  }



}//---------/ Class KwikUtils
