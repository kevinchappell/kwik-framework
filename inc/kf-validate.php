<?php


  Class Validate extends KwikInputs {

    public function validateFont($val) {
      $font = array(
        'color' => $this->color($val['color']),
        'weight' => wp_filter_nohtml_kses($val['weight']),
        'size' => wp_filter_nohtml_kses($val['size']),
        'line-height' => wp_filter_nohtml_kses($val['line-height']),
        'font-family' => wp_filter_nohtml_kses($val['font-family'])
      );
      return $font;
    }

    public function linkColor($val) {
      $link_color = array(
        'default' => $this->color($val['default']),
        'visited' => $this->color($val['visited']),
        'hover' => $this->color($val['hover']),
        'active' => $this->color($val['active'])
      );

      return $link_color;
    }

    public function color($val) {
      $color = (isset($val) && preg_match('/^#?([a-f0-9]{3}){1,2}$/i', $val)) ? '#' . strtolower(ltrim($val, '#')) : '';
      return $color;
    }

    public function validateHeaders($val) {
      $headers = array();
      $utils = new KwikUtils();
      $post_types = $utils->get_all_post_types();

      foreach ($post_types as $type) {
        $headers[$type['name']] = array(
          'color' => $this->color($val[$type['name']]['color']),
          'weight' => wp_filter_nohtml_kses($val[$type['name']]['weight']),
          'size' => wp_filter_nohtml_kses($val[$type['name']]['size']),
          'line-height' => wp_filter_nohtml_kses($val[$type['name']]['line-height']),
          'font-family' => wp_filter_nohtml_kses($val[$type['name']]['font-family']),
          'bg_color' => $this->color($val[$type['name']]['bg_color']),
          'img' => wp_filter_nohtml_kses($val[$type['name']]['img']),
          'position' => wp_filter_nohtml_kses($val[$type['name']]['position']),
          'repeat' => wp_filter_nohtml_kses($val[$type['name']]['repeat']),
          'bg_size' => wp_filter_nohtml_kses($val[$type['name']]['bg_size']),
          'attachment' => wp_filter_nohtml_kses($val[$type['name']]['img']),
          'text' => wp_filter_nohtml_kses($val[$type['name']]['text'])
        );
      }

      return $headers;
    }

  }