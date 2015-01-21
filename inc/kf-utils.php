<?php

/**
 * Description: Collection of utilities for common PHP and WordPress tasks for themes and plugins
 */
class KwikUtils
{

    /**
     * does cURL to the url provided
     * @param  [String] $url
     * @return [Dynamic]      data found at $url
     */
    private function curl_get_result($url)
    {
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
    private function fetch_cached_resource($url, $type, $expire)
    {
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

    public function get_google_fonts()
    {
        $kf_options = get_option(KF_FUNC);
        $api_key = $kf_options['fonts_key'];
        $defaults_fonts = KwikInputs::default_fonts();

        if ($api_key) {
            $feed = "https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&fields=items(category%2Cfamily%2Cvariants)&key=" . $api_key;
            $fonts = json_decode($this->fetch_cached_resource($feed, 'fonts', 1200));

            if ($fonts) {
                // are there any results?
                return $fonts->items;
            } else {
                // There are no fonts... somehow
                return $defaults_fonts;
            }
        } else {
            return $defaults_fonts;
        }

    }

    /**
     * parse out the domain name from a url string
     * @param  [String] $url  'http://sub.domain.com'
     * @return [String]      'domain.com'
     */
    public function get_domain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }

    /**
     * PHP only way of getting the current page url
     * @return [String] 'http://example.com/some/page'
     */
    public function current_page_url()
    {
        $pageURL = 'http';
        $server = $_SERVER; // should not access superglobal directly
        if (isset($server["HTTPS"]) && strtolower($server["HTTPS"]) == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($server["SERVER_PORT"] != "80") {
            $pageURL .= $server["SERVER_NAME"] . ":" . $server["SERVER_PORT"] . $server["REQUEST_URI"];
        } else {
            $pageURL .= $server["SERVER_NAME"] . $server["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
     * get the number of widgets in a sidebar
     * @param  [Number]  $sidebar_id - id of the sidebar whose wigets you need a count of
     * @param  [Boolean] $echo       to echo or not to echo
     * @return [Number]              3
     */
    public function widget_count($sidebar_id, $echo = true)
    {
        $the_sidebars = wp_get_sidebars_widgets();
        if (!isset($the_sidebars[$sidebar_id])) {
            return __('Invalid sidebar ID');
        }

        if ($echo) {
            echo count($the_sidebars[$sidebar_id]);
        } else {
            return count($the_sidebars[$sidebar_id]);
        }
    }

    /**
     * get the user's IP
     * @return [String] 127.0.0.1
     */
    public function get_real_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * taxonomies can be hierarchical, find the parent without context
     * @param  [Number]  $id        current taxonomy ID
     * @param  [Object]  $taxonomy  wordpress $taxonomy obect
     * @param  boolean $link        [description]
     * @param  string  $separator   ','
     * @return [Object]             parent objects of current taxonomy
     */
    public function get_taxonomy_parents($id, $taxonomy, $link = false, $separator = '/', $nicename = false, $visited = array())
    {
        $chain = '';
        $parent = &get_term($id, $taxonomy);

        if (is_wp_error($parent)) {
            return $parent;
        }

        if ($nicename) {
            $name = $parent->slug;
        } else {
            $name = $parent->name;
        }

        if ($parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited)) {
            $visited[] = $parent->parent;
            $chain .= get_taxonomy_parents($parent->parent, $taxonomy, $link, $separator, $nicename, $visited);
        }

        if ($link) {
            // nothing, can't get this working :(
        } else {
            $chain .= $name . $separator;
        }

        return $chain;
    }

    /**
     * Attempts to get the featured image for a given post
     * if no featured image is set, one will be chosen from
     * images attached to post, if none are attached it will
     * randomly choose an image form the media library
     *
     * @param  [boolean] $random_fallback   - use random image from library if non available for current post
     * @param  [boolean] $echo              - echo the output?
     * @return [String]                     - <img> tag
     */
    public function featured_image($random_fallback = false, $echo = true)
    {
        $post_id = get_the_id();
        if (has_post_thumbnail()) {
            $thumb = get_the_post_thumbnail($post_id, 'thumbnail');
        } else {
            $attached_image = get_children("post_parent=" . $post_id . "&post_type=attachment&post_mime_type=image&numberposts=1");
            if ($attached_image) {
                $thumb = wp_get_attachment_image(key((array) $attached_image), 'thumbnail');
            } else if ($random_fallback) {
                $args = array(
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'post_status' => 'inherit',
                    'posts_per_page' => 1,
                    'orderby' => 'rand',
                );
                $query_images = new WP_Query($args);
                $thumb = wp_get_attachment_image($query_images->posts[0]->ID, 'thumbnail');
            }
        }
        if (!$echo) {
            return $thumb;
        } else {
            echo $thumb;
        }
    }

    /**
     * add, update or delete post meta
     * @param  [Number] $post_id  eg. 123
     * @param  [String] $field_name key of the custom field to be updated
     * @param  string $value
     */
    public function update_meta($post_id, $field_name, $value = '')
    {
        if (empty($value) or !$value) {
            delete_post_meta($post_id, $field_name);
        } elseif (!get_post_meta($post_id, $field_name)) {
            add_post_meta($post_id, $field_name, $value);
        } else {
            update_post_meta($post_id, $field_name, $value);
        }
    }

    /**
     * returns and array of all `_builtin` and custom post types
     * @return [Array]
     */
    public function get_all_post_types()
    {
        $all_post_types = array();
        $args = array(
            'public' => true,
            '_builtin' => true,
        );
        $output = 'objects'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $default_post_types = get_post_types($args, $output, $operator);

        foreach ($default_post_types as $k => $v) {
            $all_post_types[$k]['label'] = $v->labels->name;
            $all_post_types[$k]['name'] = $v->name;
        }

        $args = array(
            'public' => true,
            '_builtin' => false,
        );

        $custom_post_types = get_post_types($args, $output, $operator);

        foreach ($custom_post_types as $k => $v) {
            $all_post_types[$k]['label'] = $v->labels->name;
            $all_post_types[$k]['name'] = $v->name;
        }

        array_push($all_post_types, array('name' => '404', 'label' => __('404 Not Found', 'kwik')));

        return $all_post_types;
    }

    /**
     * convert a number to english
     * @param  [Number]  $num
     * @param  Boolean $echo
     * @return [String]        'zero'
     */
    public function number_to_string($num, $echo = false)
    {
        $numbers = array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten');
        if ($echo) {
            echo $numbers[$num];
        } else {
            return $numbers[$num];
        }
    }

    /**
     * number to english fraction
     * @param  [Number]   $num    2
     * @param  boolean    $echo
     * @return [String]           halves
     */
    public function number_to_class($num, $echo = false)
    {
        $numbers = array('', 'one', 'halves', 'thirds', 'fourths', 'fifths', 'sixths', 'sevenths');
        if ($echo) {
            echo $numbers[$num];
        } else {
            return $numbers[$num];
        }
    }

    /**
     * text truncation by hard limit or by word
     * @param  [String]   $str    'Lorem ipsum dolemite'
     * @param  [Number]   $n      10
     * @param  string     $delim  '&hellip'
     * @param  boolean    $neat   trim by word
     * @return [String]             Lorem ipsum...
     */
    public static function neat_trim($str, $n, $delim = '&hellip;', $neat = true)
    {
        $len = strlen($str);
        if ($len > $n) {
            if ($neat) {
                preg_match('/(.{' . $n . '}.*?)\b/', $str, $matches);
                return rtrim($matches[1]) . $delim;
            } else {
                return substr($str, 0, $n) . $delim;

            }
        } else {
            return $str;
        }
    }

    /**
     * Starts building the settings page and section for plugin or theme
     * @param  [String] $name     'my-plugin'
     * @param  [String] $page     'my-plugin-settings'
     * @param  [Array] $settings  default settings array
     */
    public function settings_init($name, $page, $settings)
    {
        $validate = new KwikValidate($settings);
        wp_enqueue_script('jquery-ui-tabs');
        $options = get_option($page);
        foreach ($settings as $section => $val) {
            register_setting($page, $page, array($validate, 'validate_settings'));
            add_settings_section(
                $section, // section id
                $val['section_title'],
                $val['section_desc'], // callback for section
                $page
            );
            $this->add_kf_fields($val['settings'], $section, $page, $settings);
        }
    }

    /**
     * registers fields to sections of your settings page
     * @param [Array] $fields     array of fields for current section
     * @param [String] $section
     * @param [String] $page
     * @param [Array] $settings default settings array to iterate through
     */
    private function add_kf_fields($fields, $section, $page, $settings)
    {
        foreach ($fields as $k => $v) {
            $current_field = $settings[$section]['settings'][$k];
            $desc = isset($current_field['desc']) ? $current_field['desc'] : null;
            if (!isset($v['type']) || (isset($v['type']) && $v['type'] === 'multi')) {
                $v['type'] = 'multi';
                $args = array(
                    'fields' => $current_field['fields'],
                    'desc' => $desc,
                );
                $callback = 'multi';
            } else {
                $args = array(
                    'value' => isset($current_field['value']) ? $current_field['value'] : null,
                    'options' => isset($current_field['options']) ? $current_field['options'] : null,
                    'attrs' => isset($current_field['attrs']) ? $current_field['attrs'] : null,
                    'desc' => $desc,
                );

                $callback = $v['type'];
            }
            add_settings_field(
                $k, // id
                $v['title'], // title
                $callback, //callback, type or multi to insert multiple fields in single settings
                $page,
                $section, // section
                $args
            );
            $current_field['desc'] = '';
        }
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
        $errors = get_settings_errors();
        $output = '';

        global $wp_settings_fields;
        if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section])) {
            return;
        }

        $sectionFields = (array) $wp_settings_fields[$page][$section];

        foreach ($sectionFields as $field) {
            $error_class = '';
            $id = esc_attr($field['id']);
            $type = $field['callback'];

            $title = $field['title'];

            if ($field['args']['desc']) {
                $desc = $inputs->markup('span', '', array('class' => 'dashicons ks_info_tip', 'tooltip' => $field['args']['desc']));
                $title .= ' ' . $desc;
            }

            $setting_error = get_settings_errors($id);

            if (isset($setting_error[0])) {
                $error_icon = $inputs->markup('span', '!', array('class' => 'error_icon', 'tooltip' => $setting_error[0]['message']));
                $title = $title . $error_icon;
                $error_class = 'error';
            }

            if (!empty($field['args']['label_for'])) {
                $field['title'] = $inputs->markup('label', $title, array('for' => $field['args']['label_for']));
            }

            $th = $inputs->markup('th', $title, array('scope' => 'row'));
            $value = isset($settings[$field['id']]) ? $settings[$field['id']] : $field['args']['value'];

            if ($field['callback'] === 'multi') {
                $field = $inputs->$field['callback'](
                    $page . '[' . $id . ']', // name
                    $value, // value`
                    $field['args']
                );
            } else {
                $field = $inputs->$field['callback'](
                    $page . '[' . $id . ']', // name
                    $value, // value
                    null, // label
                    $field['args']['attrs'],
                    $field['args']['options']// options
                );
            }

            $td = $inputs->markup('td', $field);
            $output .= $inputs->markup('tr', $th . $td, array('valign' => 'top', 'class' => array($id, KF_PREFIX . 'option', 'type-' . $type, $error_class)));

        }
        return $output;
    }

    /**
     * Adds a custom post to to the admin dashboard `At a Glance`
     * @param  [String] $cpt  name of the custom post type to be added
     * @return [String]       markup for custom at a glance dashboard widgets
     */
    public static function cpt_at_a_glance($cpt)
    {
        $post_type = get_post_type_object($cpt);
        $num_posts = wp_count_posts($post_type->name);
        $num = number_format_i18n($num_posts->publish);
        $text = _n($post_type->labels->singular_name, $post_type->labels->name, intval($num_posts->publish));
        echo '<li class="' . $cpt . '-count"><tr><a href="edit.php?post_type=' . $cpt . '"><td class="first b b-' . $cpt . '"></td>' . $num . ' <td class="t ' . $cpt . '">' . $text . '</td></a></tr></li>';
    }

    /**
     * generate text styles
     * @param  [Array] $option  array('color' => #333333, 'style' => array('Bold'=>'bold'))
     * @return [String]         css
     */
    public static function text_style($option)
    {
        $css = $option['color'] !== '' ? 'color:' . $option['color'] . ';' : '';
        $css .= isset($option['style']['Bold']) ? 'font-weight:' . $option['style']['Bold'] . ';' : '';
        $css .= isset($option['style']['Underlined']) ? 'text-decoration:' . $option['style']['Underlined'] . ';' : '';
        $css .= isset($option['style']['Italic']) ? 'font-style:' . $option['style']['Italic'] . ';' : '';
        return $css;
    }

    public static function font_css($option)
    {
        $css = '';
        // TODO add setting to let user choose between pixel and em for font sizing
        $suffix_px = array('font-size', 'line-height');
        foreach ($option as $key => $value) {
            $suffix = '';
            if ($key === 'font-family') {
                $value = '"' . str_replace('+', ' ', $value) . '"';
            } else if (in_array($key, $suffix_px)) {
                $suffix = 'px';
            }
            $css .= $option[$key] ? $key . ':' . $value . $suffix . ';' : '';
        }
        return $css;
    }
}//---------/ Class KwikUtils
