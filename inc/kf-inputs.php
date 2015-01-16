<?php

  Class KwikInputs{

    public function positions() {
      $t = 'Top';$r = 'Right';$b = 'Bottom';$l = 'Left';$m = 'Middle';
      $c = 'Center';$z = '0';$s = ' ';$f = '50%';$o = '100%';
      $positions = array(
        $z.$s.$z => $t.$s.$l,$z.$s.$f => $t.$s.$c,$z.$s.$o => $t.$s.$r,$f.$s.$z => $m.$s.$l,
        $f.$s.$f => $m.$s.$c,$f.$s.$o => $m.$s.$r,$o.$s.$z => $b.$s.$l,$o.$s.$f => $b.$s.$c,
        $o.$s.$o => $b.$s.$r
      );
      return $positions;
    }

    public function repeat() {
      $R = 'Repeat'; $r = strtolower($R);
      return array(
        'no-'.$r => 'No '.$R,
        $r => $R,
        $r.'-x' => $R.'-X',
        $r.'-y' => $R.'-Y',
      );
    }

    public function target() {
      $target = array(
        '_blank' => __('New Window/Tab', 'kwik'),
        '_self' => __('Same Window', 'kwik')
      );
      return $target;
    }

    public function bgSize() {
      $bgSize = array(
        'auto' => __('Default', 'kwik'),
        '100% 100%' => __('Stretch', 'kwik'),
        'cover' => __('Cover', 'kwik'),
      );
      return $bgSize;
    }

    public function bgAttachment() {
      $bgAttachment = array(
        'scroll' => __('Scroll', 'kwik'),
        'fixed' => __('Fixed', 'kwik'),
      );
      return $bgAttachment;
    }

    public function fontWeights() {
      $fontWeights = array(
        'normal' => __('Normal', 'kwik'),
        'bold' => __('Bold', 'kwik'),
        'bolder' => __('Bolder', 'kwik'),
        'lighter' => __('Lighter', 'kwik')
      );
      return $fontWeights;
    }

    public function defaultFonts() {
      $fontWeights = array(
        (object) array('family' => '“Helvetica Neue”'),
        (object) array('family' => '“Baskerville Old Face”'),
        (object) array('family' => '“Trebuchet MS”'),
        (object) array('family' => '"Century Gothic"'),
        (object) array('family' => '“Courier Bold"')
      );
      return $fontWeights;
    }

    public function orderBy() {
      $orderBy = array(
        'menu_order' => __('Menu Order', 'kwik'),
        'post_title' => __('Alphabetical', 'kwik'),
        'post_date' => __('Post Date', 'kwik')
      );
      return $orderBy;
    }

    public function order() {
      $order = array(
        'ASC' => __('Ascending', 'kwik'),
        'DESC' => __('Descending', 'kwik')
      );
      return $order;
    }


    /**
     *****************************************
     * INPUTS --------------------------------
     *****************************************
    */



    /**
     * Generate markup for input field
     * @param  [Object] $attrs Object with properties for field attributes
     * @return [String]        markup for desired input field
     */
    public function input($attrs) {
      $output = '';
      $label = '';
      if($attrs['label'] && !is_null($attrs['label'])) {
        $label_attrs = array();
        if(isset($attrs['id'])){
          $label_attrs['for'] = $attrs['id'];
        }
        $label = $this->markup('label', $attrs['label'], $label_attrs);
      }
      unset($attrs['label']);
      $output .= '<input ' . $this->attrs($attrs) . ' />';

      if ($attrs['value'] && $attrs['class'] === 'cpicker') {
        $output .= $this->markup('span', NULL, array('class'=>'clear_color', 'title'=>__('Remove Color', 'kwik')));
      }

      if($attrs['type'] !== 'hidden' && !is_null($attrs['type'])){
        $output = $attrs['type'] !== 'checkbox' ? $label.$output : $output.$label;
        $output = $this->markup('div', $output, array('class' => KF_PREFIX.'field kf_'.$attrs['type'].'_wrap'));
      }
      return $output;
    }

    /**
     * Use multiple fields for a single option. Useful for generating
     * width/height or geocoordinates array.
     * @param  [String]   $name   name of the field or option
     * @param  [Dynamic]  $value  [description]
     * @param  [Array]    $args   Holds the Array of inputs to be generated
     * @return [String]           Genearated markup of inputs sharing a single name
     */
    public function multi($name, $value, $args) {
      $output = '';
      $fields = $args['fields'];
      foreach ($fields as $k => $v) {
        $val = isset($value[$k]) ? $value[$k] : $args['fields'][$k]['value'];
        $v['options'] = isset($v['options']) ? $v['options'] : NULL;
        $v['attrs'] = isset($v['attrs']) ? $v['attrs'] : NULL;

        $output .= $this->$v['type'](
          $name.'['.$k.']', // name
          isset($value[$k]) ? $value[$k] : NULL, // value
          $v['title'], // label
          $v['attrs'], // array or attributes
          $v['options'] // array of `<options>` if this is a `<select>`
        );
      }

      return self::markup('div', $output, array('class' => 'kf_field kf_multi_field'));
    }


    /**
     * Custom image input that uses the wordpress media library for uploading and storage
     * @param  [string] $name  name of input
     * @param  [string] $val   id of stored image
     * @param  [string] $label
     * @param  [array]  $attrs additional attributes. Can customize size of image.
     * @return [string] returns markup for image input field
     */
    public function img($name, $val, $label, $attrs = NULL) {
      if(!$attrs){
        $attrs = array();
      }
      wp_enqueue_media();
      $output = '';
      if($val && !empty($val)){
        if($attrs['img_size']){
          $img_size = $attrs['img_size'];
          unset($attrs['img_size']);
        } else {
          $img_size = 'thumbnail';
        }
        $thumb = wp_get_attachment_image_src($val, $img_size);
        $thumb = $thumb['0'];
        $img_title = get_the_title($val);
        $remove_img = $this->markup('span', NULL, array('title'=>__('Remove Image', 'kwik'), 'class' => 'clear_img tooltip') );
      }
      $defaultAttrs = array(
        'type' => 'hidden',
        'name' => $name,
        'class' => 'img_id',
        'value' => $val,
        'id' => $this->makeID($name)
      );
      $attrs = array_merge($defaultAttrs, $attrs);

      $img_attrs = array("src"=> $thumb, "class"=>"img_prev", "width"=>"23", "height"=>"23", "title"=>$img_title);

      $output .= $this->input($attrs);
      if($label) {
        $output .= $this->markup('label', esc_attr($label));
      }
      $output .= $this->markup('img', NULL, $img_attrs);
      if($thumb){
        $img_ttl = get_the_title($val);
        $img_ttl = $img_ttl.$this->markup('span', NULL, array( "class" => "clear_img", "tooltip" => __('Remove Image', 'kwik')));
      } else {
        $img_ttl = NULL;
      }
      $output .= $this->markup('span', $img_ttl, array('class'=>"img_title"));
      $output .= $this->markup('button', '+ '.__('IMG', 'kwik'), array('class'=>"upload_img", "type"=>"button"));
      $output = $this->markup('div', $output, array('class'=>KF_PREFIX.'field kf_img_wrap'));
      return $output;
    }

    public function text($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';
      $defaultAttrs =   array(
        'type' => 'text',
        'name' => $name,
        'class' => KF_PREFIX.'text '. $this->makeID($name),
        'value' => $val,
        // 'id' => $this->makeID($name),
        'label' => esc_attr($label)
      );

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output .= $this->input($attrs);

      return $output;
    }

    public function link($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';

      $defaultAttrs =   array(
        'type' => 'text',
        'name' => $name."[url]",
        'class' => KF_PREFIX.'link '.$this->makeID($name),
        'value' => $val['url'],
        // 'id' => $this->makeID($name)
      );

      if(!is_null($attrs)){
        $attrs = array_merge($defaultAttrs, $attrs);
      }

      if($label) {
        $attrs['label'] = esc_attr($label);
      }

      $output .= $this->input($attrs);
      $output .= $this->select($name."[target]", $val['target'], NULL, NULL, $this->target());
      $output = $this->markup('div', $output, array('class'=>KF_PREFIX.'link_wrap'));

      return $output;
    }

    public function nonce($name, $val) {
      $attrs = array(
        'type' => 'hidden',
        'name' => $name,
        'value' => $val,
      );
      return $this->input($attrs);
    }

    public function spinner($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';
      $defaultAttrs = array(
        'type' => 'number',
        'name' => $name,
        'class' => KF_PREFIX.'spinner',
        'max' => '50',
        'min' => '1',
        'value' => $val,
        'label'=> esc_attr($label)
      );

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output .= $this->input($attrs);

      return $output;
    }

    public function color($name, $val, $label = NULL) {
      $output = '';
      wp_enqueue_script('cpicker', KF_URL . '/js/cpicker.min.js');

      $attrs = array(
        'type' => 'text',
        'name' => $name,
        'class' => 'cpicker',
        'value' => $val,
        'id' => $this->makeID($name),
        'label' => esc_attr($label)
      );
      $output .= $this->input($attrs);

      $output = $this->markup('div', $output, array('class'=> array(KF_PREFIX.'field_color', KF_PREFIX.'field')));

      return $output;
    }

    public function toggle($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';

      wp_enqueue_script('kcToggle-js', 'http://kevinchappell.github.io/kcToggle/kcToggle.js', array('jquery'));
      wp_enqueue_style('kcToggle-css', 'http://kevinchappell.github.io/kcToggle/kcToggle.css', false);

      $defaultAttrs = array(
        'type' => 'checkbox',
        'name' => $name,
        'class' => 'kcToggle',
        'value' => $val || true,
        'id' => $this->makeID($name),
        'label' => esc_attr($label),
        'kcToggle' => NULL
      );

      if(!is_null($val) && $val !== ""){
        $defaultAttrs["checked"] = "checked";
      }

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output .= $this->input($attrs);
      $output = $this->markup('div', $output, array('class'=>'kf_field_toggle'));

      return $output;
    }

    public function cb($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';

      $defaultAttrs = array(
        'type' => 'checkbox',
        'name' => $name,
        'value' => $val,
        // 'id' => $this->makeID($name),
        'label' => esc_attr($label)
      );

      if(!is_null($val) && $val !== '' && $attrs['checked'] !== FALSE){
        $defaultAttrs['checked'] = NULL;
      } else {
        unset($attrs['checked']);
      }

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output .= $this->input($attrs);

      return $output;
    }

    public function cb_group($name, $val, $label = NULL, $attrs = NULL, $options) {
      $output = '';
      $defaultAttrs = array(
        'class' => KF_PREFIX.'checkbox-group '.$this->makeID($name),
        'id' => $this->makeID($name)
      );

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      if($label) {
        $output .= $this->markup('label', esc_attr($label), array( 'for' => $attrs['id']));
      }

      foreach ($options as $k => $v) {
        $attrs['checked'] = $val[$k] ? TRUE : FALSE;
        $attrs['id'] = $defaultAttrs['id'].'-'.$v;
        $output .= $this->cb($name.'['.$k.']', $v, $k, $attrs);
      }

      $output = $this->markup('div', $output, array('class' => KF_PREFIX.'field '.KF_PREFIX.'checkbox-group-wrap'));

      return $output;
    }

    public function select($name, $val, $label = NULL, $attrs = NULL, $optionsArray) {
      $defaultAttrs = array(
        'name' => $name,
        'class' => KF_PREFIX.'select '.$this->makeID($name),
        'id' => $this->makeID($name)
      );

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output = '';

      if($label) {
        $output .= $this->markup('label', esc_attr($label), array( 'for' => $attrs['id']));
      }
        $options = '';

        foreach ($optionsArray as $k => $v) {
          $oAttrs = array('value' => $k);
          if ($val === $k) {
            $oAttrs['selected'] = 'selected';
          }
          $options .= $this->markup('option', $v, $oAttrs);
        }

      $output .= $this->markup('select', $options, $attrs);
      $output = $this->markup('div', $output, array('class'=>KF_PREFIX.'field '.KF_PREFIX.'select_wrap'));

      return $output;
    }


    public function font($name, $val, $label = NULL) {
      $output = '';
      $fields = array(
        'fields' => array(
          'color' => array(
            'type'=> 'color',
            'title'=> 'Color:',
            'value'=> $val['color']
            ),
          'font-weight' => array(
            'type' => 'select',
            'title' => __('Weight', 'kwik'),
            'value' => $val['font-weight'],
            'options' => $this->fontWeights()
            ),
          'font-size' => array(
            'type' => 'spinner',
            'title'=> __('Size', 'kwik'),
            'value'=> $val['font-size']
            ),
          'line-height' => array(
            'type' => 'spinner',
            'title' => __('Line-Height', 'kwik'),
            'value' => $val['line-height']
            ),
          'font-family' => array(
            'type' => 'font_family',
            'title'=> __('Font-Family', 'kwik'),
            'value' => $val['font-family']
            )
          )
        );

      if($label) {
        $output .= $this->markup('label', esc_attr($label));
      }

      $output .= $this->multi($name, $val, $fields);

      return $output;
    }

    public function font_family($name, $val, $label = NULL) {
      $utils = new KwikUtils();
      $fonts = $utils->get_google_fonts();
      $options = array();
      foreach ($fonts as $font) {
        $key = str_replace(' ', '+', $font->family);
        $options[$key] = $font->family;
      }
      return $this->select($name, $val, $label, NULL, $options);
    }


    /**
     * Takes an array of attributes and expands and returns them formatted for markup
     * @param  [Array] $attrs Array of attributes
     * @return [String]       attributes as strings ie. `name="the_name" class="the_class"`
     */
    private function attrs($attrs) {
      $output = '';
      if (is_array($attrs)) {
        if(isset($attrs['label'])) {
          unset($attrs['label']);
        }
        foreach ($attrs as $key => $val) {
          if (is_array($val)) {
            $val = implode(" ", $val);
          } elseif(!$val) {
            $val = ' ';
          }
          if($val !== ' ') $val = '="'.esc_attr($val).'" ';
          $output .= $key . $val;
        }
      }
      return $output;
    }

    private function makeID($string){
      $string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
      return trim(preg_replace('/-+/', '-', $string), '-');
    }

    public function markup($tag, $content = NULL, $attrs = NULL){
      $no_close_tags = array('img', 'hr', 'br', 'link'); $no_close = in_array($tag, $no_close_tags);

      $markup = '<'.$tag.' '.self::attrs($attrs).' '.($no_close ? '/' : '').'>';
      if($content){
        $c = '';
        if(is_array($content)){
          foreach ($content as $key => $value) {
            if(is_array($value)){
              $c .= implode($value);
            } elseif (is_string($value)) {
              $c .= $value;
            }
          }
        } else{
          $c = $content;
        }
        $markup .= $c;
      }
      if(!$no_close) $markup .= '</'.$tag.'>';

      return $markup;
    }


  }//---------/ Class KwikInputs
