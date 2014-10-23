<?php

  Class KwikInputs{

    public function positions() {
      $positions = array(
        '0 0' => 'Top Left',
        '0 50%' => 'Top Center',
        '0 100%' => 'Top Right',
        '50% 0' => 'Middle Left',
        '50% 50%' => 'Middle Center',
        '50% 100%' => 'Middle Right',
        '100% 0' => 'Bottom Left',
        '100% 50%' => 'Bottom Center',
        '100% 100%' => 'Bottom Right',
      );
      return $positions;
    }

    public function repeat() {
      $R = 'Repeat';
      $r = 'repeat';
      $repeat = array(
        'no-'.$r => 'No '.$R,
        $r => $R,
        $r.'-x' => $R.'-X',
        $r.'-y' => $R.'-Y',
      );
      return $repeat;
    }

    public function target() {
      $target = array(
        '_blank' => 'New Window/Tab',
        '_self' => 'Same Window'
      );
      return $target;
    }

    public function bgSize() {
      $bgSize = array(
        'auto' => 'Default',
        '100% 100%' => 'Stretch',
        'cover' => 'Cover',
      );
      return $bgSize;
    }

    public function bgAttachment() {
      $bgAttachment = array(
        'scroll' => 'Scroll',
        'fixed' => 'Fixed',
      );
      return $bgAttachment;
    }

    public function fontWeights() {
      $fontWeights = array(
        'normal' => 'Normal',
        'bold' => 'Bold',
        'bolder' => 'Bolder',
        'lighter' => 'Lighter',
      );
      return $fontWeights;
    }


    /**
     * Generate markup for input field
     * @param  [Object] $attrs Object with properties for field attributes
     * @return [String]        markup for desired input field
     */
    private function input($attrs) {
      $output = '';
      if($attrs->label) {
        $output .= $this->markup('label', $attrs->label, (object) array( 'for' => $attrs->id));
        unset($attrs->label);
      }
      $output .= '<input ' . $this->attrs($attrs) . ' />';
      return $output;
    }

    public function img($name, $val, $label) {

      wp_enqueue_media();
      $output = '';
      if($val){
        $thumb = wp_get_attachment_image_src($val, 'thumbnail');
        $thumb = $thumb['0'];
      }
      $attrs = (object) array(
        'type' => 'hidden',
        'name' => $name,
        'class' => 'img_id',
        'value' => $val,
        'id' => $this->makeID($name)
      );

      if($label) {
        $attrs->label = esc_attr($label);
      }
      $output .= $this->input($attrs);
      $img_attrs = (object) array("class"=>"img_prev", "width"=>"23", "height"=>"23", "title"=>get_the_title($val));
      $output .= $this->markup('img', NULL, $img_attrs);
      $output .= '<span id="site_bg_img_ttl" class="img_title">' . get_the_title($val) . (!empty($val) ? '<span title="' . __('Remove Image', 'kwik') . '" class="clear_img tooltip"></span>' : '') . '</span><input type="button" class="upload_img" id="upload_img" value="+ ' . __('IMG', 'kwik') . '" />';
      return $output;
    }

    public function text($name, $val, $label = NULL) {
      $output = '';
      $attrs = (object) array(
        'type' => 'text',
        'name' => $name,
        'class' => 'op_text',
        'value' => $val,
        'id' => $this->makeID($name)
      );

      if($label) {
        $attrs->label = esc_attr($label);
      }

      $output .= $this->input($attrs);
      return $output;
    }

    public function nonce($name, $val) {
      $attrs = (object) array(
        'type' => 'hidden',
        'name' => $name,
        'value' => $val,
      );
      return $this->input($attrs);
    }

    public function spinner($name, $val, $label = NULL) {
      $output = '';
      $attrs = (object) array(
        'type' => 'number',
        'name' => $name,
        'class' => 'kf_spinner',
        'max' => '50',
        'min' => '1',
        'value' => $val,
        'label'=> $label
      );

      if($label) {
        $attrs->label = esc_attr($label);
      }
      $output .= $this->input($attrs);

      return $output;
    }

    public function color($name, $val, $label = NULL) {
      $output = '';
      wp_enqueue_script('cpicker', KF_URL . '/js/cpicker.js');

      $attrs = (object) array(
        'type' => 'text',
        'name' => $name,
        'class' => 'cpicker',
        'value' => $val,
        'id' => $this->makeID($name)
      );
      if($label) {
        $attrs->label = esc_attr($label);
      }
      $output .= $this->input($attrs);
      if (!empty($val)) {$output .= '<span class="clear_color tooltip" title="' . __('Remove Color', 'kwik') . '"></span>';
      }

      return $output;
    }

    public function select($name, $val, $options, $label = NULL) {

      $attrs = (object) array(
        'name' => $name,
        'class' => 'kf_select',
        'id' => $this->makeID($name)
      );

      if($label) {
        $output .= $this->markup('label', $label, (object) array( 'for' => $attrs->id));
      }
      $output = '<select ' . $this->attrs($attrs) . '">';
      foreach ($options as $k => $v) {
        $output .= '<option ' . selected($k, $val, false) . ' value="' . $k . '">' . $v . '</option>';
      }

      $output .= '</select>';
      return $output;
    }

    public function fontFamily($name, $val) {
      $utils = new KwikUtils();
      $fonts = $utils->get_google_fonts($api_key);  // TODO: Api key from settings
      $options = array();
      foreach ($fonts as $font) {
        $options[str_replace(' ', '+', $font->family)] = $font->family;
      }
      return $this->select($name, $val, $options);
    }



    private function attrs($attrs) {
      $output = '';
      if (is_object($attrs)) {
        if($attrs->label) {
          unset($attrs->label);
        }
        foreach ($attrs as $key => $val) {
          if (is_array($val)) {
            $val = implode(" ", $val);
          }
          $output .= $key . '="' . esc_attr($val) . '" ';
        }
      }
      return $output;
    }

    private function makeID($string){
      $string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
      return trim(preg_replace('/-+/', '-', $string), '-');
    }

    public function markup($tag, $content = NULL, $attrs = NULL){
      if($attrs) {
        $attrs = $this->attrs($attrs);
      }

      $markup = '<'.$tag.' '.$attrs.' '.($tag === 'img' ? '/' : '').'>';
      if($content) $markup .= $content . ($tag === 'label' ? ':' : '');
      if($tag !== 'img') $markup .= '</'.$tag.'>';


      return $markup;
    }

  }//---------/ Class KwikInputs

  
