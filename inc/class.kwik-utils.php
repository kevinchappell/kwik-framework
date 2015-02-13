<?php

/**
 * Description: Collection of utilities for common PHP and WordPress tasks for themes and plugins
 */
class KwikUtils
{

    /**
     * does cURL to the url provided
     * @param  [String]  $url
     * @return [Dynamic] data found at $url
     */
    private function curl_get_result($url)
    {
        $curl = curl_init();
        $timeout = 5;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * fetch a resource using cURL then cache for next use.
     * @param  [String] $url  - url of the resource to be fetched
     * @param  [String] $type - type of resource to be fetched (fonts, tweets, etc)
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
        $defaults_fonts = KwikHelpers::default_fonts();

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
     * @param  [String] $url           'http://sub.domain.com'
     * @return [String] 'domain.com'
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
     * @return [Number]  3
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
        $http_client_ip = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_STRING);
        $http_x_forwarded_for = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_STRING);
        $http_client_ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);
        if (!empty($server['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip_addr = $server['HTTP_CLIENT_IP'];
        } elseif (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip_addr = $server['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_addr = $server['REMOTE_ADDR'];
        }
        return $ip_addr;
    }

    /**
     * taxonomies can be hierarchical, find the parent without context
     * @param  [number]  $tax_id    current taxonomy ID
     * @param  [object]  $taxonomy  wordpress $taxonomy obect
     * @param  [boolean] $link      [description]
     * @param  [string]  $separator ','
     * @return [object]  parent objects of current taxonomy
     */
    public function get_taxonomy_parents($tax_id, $taxonomy, $link = false, $separator = '/', $nicename = false, $visited = array())
    {
        $chain = '';
        $parent = &get_term($tax_id, $taxonomy);

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
     * returns an array of all `_builtin` and custom post types
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
        $arg['_builtin'] = false;
        $custom_post_types = get_post_types($args, $output, $operator);
        $post_types = array_merge($default_post_types, $custom_post_types);
        foreach ($post_types as $k => $v) {
            $all_post_types[$k] = array(
                'label' => $v->labels->name,
                'name' => $v->name,
            );
        }

        array_push($all_post_types, array('name' => '404', 'label' => __('404 Not Found', 'kwik')));

        return $all_post_types;
    }

    /**
     * Convert a number to english
     * @param  [int]      $num  number to convert
     * @param  [boolean[] $echo echo or return
     * @return [string] 'zero' string that is returned
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
     * Number to english fraction
     * @param  [Number] $num 2
     * @param  boolean  $echo
     * @return [String] halves
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
     * Text truncation by hard limit or by word
     * @param  [String] $str   'Lorem ipsum dolemite'
     * @param  [Number] $n     10
     * @param  string   $delim '&hellip'
     * @param  boolean  $neat  trim by word
     * @return [String] Lorem ipsum...
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
     * Adds a custom post to to the admin dashboard `At a Glance`
     * @param  [String] $cpt name of the custom post type to be added
     * @return [String] markup for custom at a glance dashboard widgets
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
     * Generate text styles
     * @param  [Array] $option array('color' => #333333, 'style' => array('Bold'=>'bold'))
     * @return [String] css
     */
    public static function text_style($option)
    {
        $option = array_filter($option);
        $css = '';
        array_walk($option, array('KwikHelpers', 'css_map'));

        foreach ($option as $key => $value) {
            $css .= $key.':'.$value.";\n";
        }

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
            $css .= $option[$key] ? '    '.$key . ':' . $value . $suffix . ";\n" : '';
        }
        return $css;
    }
}//---------/ Class KwikUtils
