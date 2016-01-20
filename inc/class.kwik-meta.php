<?php

class KwikMeta {
	private $__fields;
	static $field_group;

	public function __construct() {

	}

	public static function get_meta_array( $post_id, $key ) {
		$meta_array = get_post_meta( $post_id, $key, false );
		return ( is_array( $meta_array ) && isset( $meta_array[0] ) ) ? $meta_array[0] : $meta_array;
	}

	public static function get_fields( $post, $field_group, $fields = null ) {
		if ( $fields ) {
			set_transient( $field_group, $fields, WEEK_IN_SECONDS );
		} else {
			$fields = get_transient( $field_group );
		}
		$flat_fields = self::flattened_field_array( $fields );
		$field_vals = self::get_meta_array( $post->ID, $field_group );
		$flat_field_vals = self::merge_vals( $flat_fields, $field_vals );

		return self::generate_fields( $flat_field_vals, $field_group );
	}

	private static function merge_vals( $fields, $field_vals ) {
		foreach ( $fields as $key => $value ) {
			$default_val = isset( $fields[ $key ]['value'] ) ? $fields[ $key ]['value'] : '';
			$fields[ $key ]['value'] = isset( $field_vals[ $key ] ) ? $field_vals[ $key ] : $default_val;
		}
		return $fields;
	}

	public static function flattened_field_array( $fields, &$fields_array = array() ) {
		foreach ( $fields as $key => $value ) {
			if ( isset( $fields[ $key ]['fields'] ) ) {
				self::flattened_field_array( $fields[ $key ]['fields'], $fields_array );
			} else {
				$fields_array[ $key ] = $fields[ $key ];
			}
		}
		return $fields_array;
	}

	public static function generate_fields( $fields, $field_group ) {
		$inputs = new KwikInputs();
		$output = $inputs->nonce( $field_group . '_nonce', wp_create_nonce( $field_group ) );

		foreach ( $fields as $key => $value ) {
			$name = $field_group . '[' . $key . ']';
			$field_options = isset( $fields[ $key ]['options'] ) ? $fields[ $key ]['options'] : null;
			$field_attrs = isset( $fields[ $key ]['attrs'] ) ? $fields[ $key ]['attrs'] : null;
			$field_title = isset( $fields[ $key ]['title'] ) ? $fields[ $key ]['title'] : null;
			$output .= $inputs->$fields[ $key ]['type']($name, $fields[ $key ]['value'], $field_title, $field_attrs, $field_options);
		}

		return $output;
	}

	/**
	 * builds a values array for only fields set with the transient
	 * @param [object] $post        Post object to the meta is for
	 * @param [string] $field_group namespace this meta data belongs to
	 */
	public function save_meta( $the_post, $field_group ) {
		$post = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		if ( isset( $post[ $field_group . '_nonce' ] ) && ! wp_verify_nonce( $post[ $field_group . '_nonce' ], $field_group ) ) {
			return $the_post->ID;
		}
		if ( ! isset( $post[ $field_group ] ) ) {
			return $the_post;
		}
		$field_vals = array();
		$fields = get_transient( $field_group );
		foreach ( $fields as $key => $value ) {
			$default_val = isset( $fields[ $key ]['value'] ) ? $fields[ $key ]['value'] : '';
			$field_vals[ $key ] = isset( $post[ $field_group ][ $key ] ) ? $post[ $field_group ][ $key ] : $default_val;
		}

		$this->update_meta( $the_post->ID, $field_group, $field_vals );
	}

	/**
	 * add, update or delete post meta
	 * @param [Number] $post_id    eg. 123
	 * @param [String] $field_name key of the custom field to be updated
	 * @param [String]   $value
	 */
	public static function update_meta( $post_id, $field_name, $value = '' ) {
		if ( empty( $value ) || ! $value ) {
			delete_post_meta( $post_id, $field_name );
		} else {

			update_post_meta( $post_id, $field_name, $value );
		}
	}
}
