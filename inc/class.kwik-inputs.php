<?php

class KwikInputs {

	/**
	 * ****************************************
	 * INPUTS --------------------------------
	 * ****************************************
	 */

	/**
	 * Generate markup for input field
	 * @param  [Object] $attrs Object with properties for field attributes
	 * @return [String] markup for desired input field
	 */
	public function input( $attrs ) {
		$output = '';
		$label = '';
		$classes = array(
			KF_PREFIX . 'field',
			$this->make_id( $attrs['name'] ),
		);

		if ( isset( $attrs['prev_img'] ) && ! is_null( $attrs['prev_img'] ) ) {
			$prev_img_url = wp_get_attachment_thumb_url( $attrs['prev_img'] );
			$output .= $this->markup( 'img', null, array( 'class' => 'kf_prev_img', 'src' => $prev_img_url ) );
		}

		if ( isset( $attrs['label'] ) && ! is_null( $attrs['label'] ) ) {
			$label_attrs = array();
			if ( isset( $attrs['id'] ) ) {
				$label_attrs['for'] = $attrs['id'];
			}
			$label = $this->markup( 'label', $attrs['label'], $label_attrs );
		}
		unset( $attrs['label'] );
		$output .= '<input ' . $this->attrs( $attrs ) . ' />';

		if ( isset( $attrs['value'] ) && ( isset( $attrs['class'] ) && 'cpicker' === $attrs['class'] ) ) {
			$output .= $this->markup( 'span', null, array( 'class' => 'clear_color', 'title' => __( 'Remove Color', 'kwik' ) ) );
		}

		if ( 'hidden' !== $attrs['type'] && ! is_null( $attrs['type'] ) ) {
			$classes[] = KF_PREFIX . $attrs['type'] . '_wrap';
			$output = $attrs['type'] !== 'checkbox' ? $label . $output : $output . $label;
			$output = $this->markup( 'div', $output, array( 'class' => $classes ) );
		}
		return $output;
	}

	/**
	 * Use multiple fields for a single option. Useful for generating
	 * width/height or geocoordinates array. Can be called recursively
	 * @param  [String]  $name      name of the field or option
	 * @param  [Dynamic] $value     [description]
	 * @param  [Array]   $args      Holds the Array of inputs to be generated
	 * @return [String]  Genearated markup of inputs sharing a single name
	 */
	public function multi( $name, $value, $label, $attrs = null, $options = null ) {
		$output = '';
		if ( ! isset( $attrs['fields'] ) ) {
			return;
		}
		$fields = $attrs['fields'];
		unset( $attrs['fields'] );
		foreach ( $fields as $k => $v ) {
			$type = isset( $v['type'] ) ? $v['type'] : 'multi';
			$val = isset( $value[ $k ] ) ? $value[ $k ] : null;
			$field_attrs = isset( $v['attrs'] ) ? $v['attrs'] : array();
			$options = isset( $v['options'] ) ? $v['options'] : null;
			$title = isset( $v['title'] ) ? $v['title'] : null;

			if ( isset( $fields[ $k ]['fields'] ) ) {
				$field_attrs['fields'] = $fields[ $k ]['fields'];
			}
			$output .= $this->$type(
				$name . '[' . $k . ']', // name
				$val, // value
				$title, // label
				$field_attrs, // array or attributes
				$options // array of `<options>` if this is a `<select>`
			);
		}

		if ( ! isset( $attrs['class'] ) ) {
			$attrs['class'] = 'kf_field kf_multi_field';
		}

		return self::markup( 'div', $output, $attrs );
	}

	/**
	 * Custom image input that uses the wordpress media library for uploading and storage
	 * @param  [string] $name    name of input
	 * @param  [string] $value   id of stored image
	 * @param  [string] $label
	 * @param  [array]  $attrs   additional attributes. Can customize size of image.
	 * @return [string] returns markup for image input field
	 */
	public function img( $name, $value, $label, $attrs = null ) {
		if ( ! $attrs ) {
			$attrs = array();
		}
		wp_enqueue_media();
		$output = '';

		$img_attrs = array( 'class' => array( 'img_prev' ) );
		if ( $value && ! empty( $value ) ) {
			if ( isset( $attrs['img_size'] ) ) {
				$img_size = $attrs['img_size'];
				unset( $attrs['img_size'] );
			} else {
				$img_size = 'thumbnail';
			}
			$thumb = wp_get_attachment_image_src( $value, $img_size );
			$thumb = $thumb['0'];
			$remove_img = $this->markup( 'span', null, array( 'title' => __( 'Remove Image', 'kwik' ), 'class' => 'clear_img tooltip' ) );
			$img_attrs['title'] = get_the_title( $value );
			$img_attrs['style'] = "background-image:url({$thumb})";
		} else {
			array_push( $img_attrs['class'], 'no-image' );
		}
		$defaultAttrs = array(
			'type' => 'hidden',
			'name' => $name,
			'class' => 'img-id',
			'value' => $value,
			'id' => $this->make_id( $name ),
			'button-text' => '+ ' . __( 'IMG', 'kwik' ),
		);
		$attrs = array_merge( $defaultAttrs, $attrs );
		$classes = array(
			KF_PREFIX . 'field',
			KF_PREFIX . 'img_wrap',
			$this->make_id( $attrs['name'] ),
		);

		$button_text = $attrs['button-text'];
		unset( $attrs['button-text'] );
		$output .= $this->input( $attrs );
		if ( $label ) {
			$output .= $this->markup( 'label', esc_attr( $label ) );
		}
		$output .= $this->markup( 'div', null, $img_attrs );
		if ( isset( $thumb ) ) {
			$img_ttl = get_the_title( $value );
			$img_ttl = $img_ttl . $this->markup( 'span', null, array( 'class' => 'clear_img', 'tooltip' => __( 'Remove Image', 'kwik' ) ) );
		} else {
			$img_ttl = null;
		}
		$output .= $this->markup( 'span', $img_ttl, array( 'class' => 'img_title' ) );
		$output .= $this->markup( 'button', $button_text, array( 'class' => 'upload_img', 'type' => 'button' ) );
		$output = $this->markup( 'div', $output, array( 'class' => $classes ) );
		return $output;
	}

	/**
	 * Wrapper for markup used by settings API
	 * @param  [string] $type      type of element
	 * @param  [string] $content   content to be output if any
	 * @param  [string] $label     Label for this element
	 * @param  [string] $attrs     array of attributes this element should have
	 * @return [type]        [description]
	 */
	public function element( $type, $content = null, $label = null, $attrs = null ) {
		$output = '';
		preg_match_all( '/\[([^\]]*)\]/', $type, $matches );
		$index = count( $matches[1] ) -1;
		$type = $matches[1][ $index ];
		if ( is_array( $attrs ) ) {
			$attrs = array_map( function( $elem ) { return esc_attr( $elem ); }, $attrs );
		}
		$defaultAttrs = array(
			'class' => KF_PREFIX . 'element ',
		);

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;
		$output .= $this->markup( esc_attr( $type ), esc_html( $content ), $attrs );

		return $output;
	}

	public function autocomplete( $name, $val, $label = null, $attrs = null ) {
		$output = '';
		$defaultAttrs = array(
			'type' => 'text',
			'name' => $name.'[label]',
			'class' => KF_PREFIX . 'autocomplete ' . $this->make_id( $name ),
			'value' => isset( $val['label'] ) ? $val['label'] : '',
			// 'id' => $this->make_id( $name ),
			'label' => esc_attr( $label ),
		);

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;
		if ( isset( $val['id'] ) && ! empty( $val['id'] ) ) {
			$attrs['prev_img'] = get_post_thumbnail_id( $val['id'] );
		}
		$output .= $this->input( $attrs );

		$cpt_id_attrs = array(
			'type' => 'hidden',
			'name' => $name.'[id]',
			'value' => isset( $val['id'] ) ? $val['id'] : '',
			'class' => 'cpt_id',
			);

		$output .= $this->input( $cpt_id_attrs );
		return $output;
	}

	public function text( $name, $val, $label = null, $attrs = null ) {
		$defaultAttrs = array(
			'type' => 'text',
			'name' => $name,
			'class' => KF_PREFIX . 'text ' . $this->make_id( $name ),
			'value' => $val,
			// 'id' => $this->make_id( $name ),
			'label' => esc_attr( $label ),
		);

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;

		return $this->input( $attrs );
	}

	public function link( $name, $val, $label = null, $attrs = null ) {
		$output = '';

		// @todo clean this up.
		$val['url'] = isset( $val['url'] ) ? $val['url'] : null;
		$val['target'] = isset( $val['target'] ) ? $val['target'] : null;

		$defaultAttrs = array(
			'type' => 'text',
			'name' => $name . '[url]',
			'class' => KF_PREFIX . 'link ' . $this->make_id( $name ),
			'value' => $val['url'],
			// 'id' => $this->make_id( $name )
		);

		if ( ! is_null( $attrs ) ) {
			$attrs = array_merge( $defaultAttrs, $attrs );
		}

		if ( $label ) {
			$attrs['label'] = esc_attr( $label );
		}

		$output .= $this->input( $attrs );
		$output .= $this->select( $name . '[target]', $val['target'], __( 'Target:','kwik' ), null, KwikHelpers::target() );
		$output = $this->markup( 'div', $output, array( 'class' => KF_PREFIX . 'link_wrap' ) );

		return $output;
	}

	public function nonce( $name, $val ) {
		$attrs = array(
			'type' => 'hidden',
			'name' => $name,
			'value' => $val,
		);
		return $this->input( $attrs );
	}

	public function spinner( $name, $val, $label = null, $attrs = null ) {
		$output = '';
		$defaultAttrs = array(
			'type' => 'number',
			'name' => $name,
			'class' => KF_PREFIX . 'spinner',
			'max' => '50',
			'min' => '1',
			'value' => $val,
			'label' => esc_attr( $label ),
		);

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;

		$output .= $this->input( $attrs );

		return $output;
	}

	public function color( $name, $val, $label = null ) {
		$output = '';
		wp_enqueue_script( 'cpicker', KF_URL . '/js/cpicker.min.js' );

		$attrs = array(
			'type' => 'text',
			'name' => $name,
			'class' => 'cpicker',
			'value' => $val,
			'id' => $this->make_id( $name ),
			'label' => esc_attr( $label ),
		);
		$output .= $this->input( $attrs );

		$output = $this->markup( 'div', $output, array( 'class' => array( KF_PREFIX . 'field_color', KF_PREFIX . 'field' ) ) );

		return $output;
	}

	public function toggle( $name, $val, $label = null, $attrs = null ) {
		$output = '';

		wp_enqueue_script( 'kcToggle-js', 'http://kevinchappell.github.io/kcToggle/kcToggle.js', array( 'jquery' ) );
		wp_enqueue_style( 'kcToggle-css', 'http://kevinchappell.github.io/kcToggle/kcToggle.css', false );

		$defaultAttrs = array(
			'type' => 'checkbox',
			'name' => $name,
			'class' => 'kcToggle',
			'value' => $val || true,
			'id' => $this->make_id( $name ),
			'label' => esc_attr( $label ),
			'kcToggle' => null,
		);

		if ( ! is_null( $val ) && $val !== '' && (isset( $attrs['checked'] ) && $attrs['checked'] === true ) || $val === '1' ) {
			$defaultAttrs['checked'] = 'checked';
		}

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;

		$output .= $this->input( $attrs );
		$output = $this->markup( 'div', $output, array( 'class' => 'kf_field_toggle' ) );

		return $output;
	}

	public function cb( $name, $val, $label = null, $attrs = null ) {
		$output = '';

		$defaultAttrs = array(
			'type' => 'checkbox',
			'name' => $name,
			'value' => $val,
			// 'id' => $this->make_id( $name ),
			'label' => esc_attr( $label ),
		);

		if ( ! is_null( $val ) && $val !== '' && (isset( $attrs['checked'] ) && $attrs['checked'] === true ) || $val === '1' ) {
			$defaultAttrs['checked'] = null;
		} else {
			unset( $attrs['checked'] );
		}

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;

		$output .= $this->input( $attrs );

		return $output;
	}

	public function cb_group( $name, $val, $label = null, $attrs = null, $options ) {
		$output = '';
		$defaultAttrs = array(
			'class' => KF_PREFIX . 'checkbox-group ' . $this->make_id( $name ),
			'id' => $this->make_id( $name ),
		);

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;

		if ( $label ) {
			$output .= $this->markup( 'label', esc_attr( $label ), array( 'for' => $attrs['id'] ) );
		}

		foreach ( $options as $k => $v ) {
			$attrs['checked'] = isset( $val[ $k ] ) ? true : false;
			$attrs['id'] = $defaultAttrs['id'] . '-' . $v;
			$output .= $this->cb( $name . '[' . $k . ']', $v, $k, $attrs );
		}

		$output = $this->markup( 'div', $output, array( 'class' => KF_PREFIX . 'field ' . KF_PREFIX . 'checkbox-group-wrap' ) );

		return $output;
	}

	public function select( $name, $val, $label = null, $attrs = null, $optionsArray ) {
		$defaultAttrs = array(
			'name' => $name,
			'class' => KF_PREFIX . 'select ' . $this->make_id( $name ),
			'id' => $this->make_id( $name ),
		);

		$attrs = ! is_null( $attrs ) ? array_merge( $defaultAttrs, $attrs ) : $defaultAttrs;

		$output = '';

		if ( $label ) {
			$output .= $this->markup( 'label', esc_attr( $label ), array( 'for' => $attrs['id'] ) );
		}
		$options = '';

		foreach ( $optionsArray as $k => $v ) {
			$oAttrs = array( 'value' => $k );
			if ( $val === $k ) {
				$oAttrs['selected'] = 'selected';
			}
			$options .= $this->markup( 'option', $v, $oAttrs );
		}

		$output .= $this->markup( 'select', $options, $attrs );
		$output = $this->markup( 'div', $output, array( 'class' => KF_PREFIX . 'field ' . KF_PREFIX . 'select_wrap' ) );

		return $output;
	}

	public function font( $name, $val, $label = null ) {
		$output = '';
		$attrs = array(
			'fields' => array(
				'color' => array(
					'type' => 'color',
					'title' => 'Color:',
					'value' => $val['color'],
				),
				'font-weight' => array(
					'type' => 'select',
					'title' => __( 'Weight', 'kwik' ),
					'value' => $val['font-weight'],
					'options' => KwikHelpers::font_weights(),
				),
				'font-size' => array(
					'type' => 'spinner',
					'title' => __( 'Size', 'kwik' ),
					'value' => $val['font-size'],
				),
				'line-height' => array(
					'type' => 'spinner',
					'title' => __( 'Line-Height', 'kwik' ),
					'value' => $val['line-height'],
				),
				'font-family' => array(
					'type' => 'font_family',
					'title' => __( 'Font-Family', 'kwik' ),
					'value' => $val['font-family'],
				),
			),
		);

		if ( $label ) {
			$output .= $this->markup( 'label', esc_attr( $label ) );
		}

		$output .= $this->multi( $name, $val, $label, $attrs );

		return $output;
	}

	public function font_family( $name, $val, $label = null ) {
		$utils = new KwikUtils();
		$fonts = $utils->get_google_fonts();
		$options = array();
		foreach ( $fonts as $font ) {
			$key = str_replace( ' ', '+', $font->family );
			$options[ $key ] = $font->family;
		}
		return $this->select( $name, $val, $label, null, $options );
	}

	/**
	 * Takes an array of attributes and expands and returns them formatted for markup
	 * @param  [Array]  $attrs     Array of attributes
	 * @return [String] attributes as strings ie. `name="the_name" class="the_class"`
	 */
	private static function attrs( $attrs ) {
		$output = '';
		if ( is_array( $attrs ) ) {
			if ( isset( $attrs['label'] ) ) {
				unset( $attrs['label'] );
			}
			foreach ( $attrs as $key => $val ) {
				if ( is_array( $val ) ) {
					$val = implode( ' ', $val );
				} elseif ( ! $val ) {
					$val = ' ';
				}
				if ( ' ' !== $val ) {
					$val = '="' . esc_attr( $val ) . '" ';
				}

				$output .= $key . $val;
			}
		}
		return $output;
	}

	private function make_id( $string ) {
		$string = preg_replace( '/[^A-Za-z0-9-]+/', '-', $string );
		return trim( preg_replace( '/-+/', '-', $string ), '-' );
	}

	public static function markup( $tag, $content = null, $attrs = null ) {
		$no_close_tags = array( 'img', 'hr', 'br', 'link' );
		$no_close = in_array( $tag, $no_close_tags );

		$markup = '<' . $tag . ' ' . self::attrs( $attrs ) . ' ' . ( $no_close ? '/' : '' ) . '>';
		if ( $content ) {
			$contents = '';
			if ( is_array( $content ) ) {
				foreach ( $content as $key => $value ) {
					if ( is_array( $value ) ) {
						$contents .= implode( $value );
					} elseif ( is_string( $value ) ) {
						$contents .= $value;
					}
				}
			} else {
				$contents = $content;
			}
			$markup .= $contents;
		}
		if ( ! $no_close ) {
			$markup .= '</' . $tag . '>';
		}

		return $markup;
	}
}//---------/ Class KwikInputs
