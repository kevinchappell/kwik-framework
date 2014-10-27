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

  // TODO: make this better,  switch case or something
  public function number_to_class($num = 1, $echo = true) {
    $class = '';
    if ($num === 1) {
      return;
    } else if ($num == 2) {
      $class = 'halves';
    } else if ($num == 3) {
        $class = 'thirds';
    } else if ($num == 4) {
        $class = 'fourths';
    } else if ($num == 5) {
        $class = 'fifths';
    }

    if (!$echo) {
      return $class;
    } else {
      echo $class;
    }

  }// ------/ number_to_class

  public function settings_init($name, $page, $settings) {

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
          'options' => $settings[$section]['settings'][$k]['options']
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
    $output .= '<div id="op_settings">';
    $output .= '<ul id="op_settings_index">';
    foreach ((array) $wp_settings_sections[$page] as $section) {
      // var_dump($section);
      $output .= $inputs->markup('li', '<a href="#' . $section['id'] . '">' . $section['title'] . '</a>');
    }
    $output .= $inputs->markup('li', get_submit_button(__('Save', 'kwik')), array("class" => 'kf_submit'));
    $output .= '</ul>';

    foreach ((array) $wp_settings_sections[$page] as $section) {
      $output .= '<div id="'.$page. '_' . $section['id'] . '" class="op_options_panel">';
      $output .= !empty($section['title']) ? "<h3>{$section['title']}</h3>\n" : "";
      // call_user_func($section['callback'], $section);
      //
      //
      $output .= $this->section_callback($section);

      if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][
      $section['id']])) {
        continue;
      }

      $output .= '<table class="form-table">';
      $output .= $this->settings_fields($page, $section['id'], $settings);
      $output .= '</table>';
      $output .= "</div>\n";
    }

    $output .= '</div>';

    return $output;
  }


  private function settings_fields($page, $section, $settings) {
    $inputs = new KwikInputs();
    global $wp_settings_fields;
    if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section])) {
      return;
    }
    $output = '';
    $sectionFields = (array) $wp_settings_fields[$page][$section];

    foreach ($sectionFields as $field) {
      $output .= '<tr valign="top">';
      if (!empty($field['args']['label_for'])) {
        $output .= '<th scope="row"><label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label></th>';
      } else {
        $output .= '<th scope="row">' . $field['title'] . '</th>';
      }

      $value = $settings[$field['id']] ? $settings[$field['id']] : $field['args']['value'];

      $output .= '<td class="' . $field['id'] . ' ' . $field['callback'] . '">';
      $selectOptions = $field['callback'] === 'select' ? $field['args']['options'] : NULL;
      $output .= $inputs->$field['callback'](
        $page.'['.$field['id'].']', // name
        $value, // value
        $selectOptions // options
        );
      // $output .= call_user_func($field['callback'], $field['args']);
      $output .= '</td>';
      $output .= '</tr>';

    }
      return $output;
  }



}//---------/ Class KwikUtils
