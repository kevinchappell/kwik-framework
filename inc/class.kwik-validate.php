<?php

class KwikValidate extends KwikInputs {

	public function __construct($settings)
	{
		$this->settings = $settings;
	}

	public function header($val)
	{
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
				'text' => wp_filter_nohtml_kses($val[$type['name']]['text']),
			);
		}

		return $headers;
	}

	/**
	 * Checks for HTML tags
	 * @param  [String]  $val
	 * @return boolean   returns true or false depending on what it finds
	 */
	private function hasHTML($val)
	{
		return (preg_match("/<[^<]+>/", $val, $m) !== 0);
	}

	/**
	 * Validate fields with type of `text`
	 * @param  [String] $key key of the field being validated
	 * @param  [Dynamic] $val Should be string but potenially user could inject some
	 * other type of variable
	 * @return [String]      Returns clean field value to be stored
	 */
	public function text($key, $val, $label = null, $attrs = null)
	{
		if ($this->hasHTML($val)) {
			add_settings_error($key, 'text', __('HTML is not allowed in this field.', 'kwik'), 'kf_error');
		}
		return wp_filter_nohtml_kses($val);
	}

	/**
	 * Validate fields with type of `cb`
	 * @param  [String] $key key of the field being validated
	 * @param  [Dynamic] $val Should be string but potenially user could inject some
	 * other type of variable
	 * @return [String]      Returns clean field value to be stored
	 */
	public function cb($key, $val, $label = null, $attrs = null)
	{
		if ($this->hasHTML($val)) {
			add_settings_error($key, 'cb', __('Checkbox value should be true or false but contains invalid characters', 'kwik'), 'kf_error');
		}
		return wp_filter_nohtml_kses($val);
	}

	/**
	 * wraps the `cb` validator
	 * @param  [String] $key
	 * @param  [Dynamic] $val
	 * @return [String]
	 */
	public function cb_group($key, $val, $label, $attrs, $options)
	{
		foreach ($val as $k => $v) {
			$val['$k'] = $this->cb($k, $v);
		}
		return $val;
	}

	/**
	 * Checks that the posted color is a valid hex color
	 * @param  [String] $key
	 * @param  [String] $val
	 * @return [String]         Color or nothing
	 */
	public function color($key, $val, $label = null)
	{
		$colorCode = ltrim($val, '#');

		if (ctype_xdigit($colorCode) && (strlen($colorCode) == 6 || strlen($colorCode) == 3)) {
			$color = $val;
		} else {
			add_settings_error($key, 'color', __('Invalid Color', 'kwik'), 'kf_error');
			$color = '';
		}

		return $color;
	}

	/**
	 * validates fields with type of font
	 * @param  [String] $key key of the field being validator
	 * @param  [Array] $val [description]
	 * @return [Array]      Array with valid indexes
	 */
	public function font($key, $val, $label = null)
	{
		$font = array(
			'color' => self::color($key, $val['color']),
			'font-weight' => self::select($key, $val['font-weight']),
			'font-size' => is_numeric($val['font-size']) ? $val['font-size'] : '',
			'line-height' => is_numeric($val['line-height']) ? $val['line-height'] : '',
			'font-family' => wp_filter_nohtml_kses($val['font-family']),
		);

		return $font;
	}

	// TODO write this validator
	public function select($key, $val, $label = null, $attrs = null, $optionsArray = null)
	{
		return $val;
	}

	public function spinner($key, $val, $label = null, $attrs = null)
	{
		if (!is_numeric($val)) {
			add_settings_error($key, 'spinner', __('Only numbers are allowed', 'kwik'), 'kf_error');
		}
		return $val;
	}

	public function img($key, $val, $label = null, $attrs = null)
	{
		if (!is_numeric($val) && !empty($val)) {
			add_settings_error($key, 'img', __('Invalid Selection', 'kwik'), 'kf_error');
		}
		return $val;
	}

	/**
	 * Validate each field in a `multi` input.
	 * @param  [string]  $name    name/key of the field
	 * @param  [dynamic] $value   string, array or boolean
	 * @param  [string]  $label   title of the field
	 * @param  [array]   $attrs   fields attributes
	 * @param  [array]   $options key value pairs for select input
	 * @return [array]            validated inputs
	 * @todo  actually validate each input...
	 */
	public function multi($name, $value, $label, $attrs = null, $options = null)
	{
		return $value;
	}

	/**
	 * Cycle through the settings `$this->settings` and build a path to
	 * the current field being validated. Once found instantiate the validator method
	 * for that field type
	 * @param  [Array] $setting values to search through
	 * @return [Array]          Validated settings array
	 */
	public function validate_settings($setting)
	{
		$settings = $this->settings;
		foreach ($setting as $key => $val) {
			$path_segments = $this->find_key($key, $this->settings);
			$addr = &$settings;
			foreach ($path_segments as $i => $path_segment) {
				$addr = &$addr[$path_segment];
			}
			if (!isset($addr['type'])) {
				$addr['type'] = 'multi';
			}

			$attrs = isset($addr['attrs']) ? : null;
			$options = isset($addr['options']) ? : null;
			$label = isset($addr['title']) ? : null;

			//validate field by type
			$setting[$key] = $this->$addr['type']($key, $val, $label, $attrs, $options);
			unset($addr);
		}
		return $setting;
	}

	/**
	 * find key in the $settings array
	 * @param  [String]  $name     name of field we are searching for
	 * @param  [Array]  $settings
	 * @param  boolean $strict
	 * @param  array   $path
	 * @return [Array]            returns a path to the setting
	 */
	public function find_key($name, $settings, $strict = false, $path = array())
	{
		if (!is_array($settings)) {
			return false;
		}

		foreach ($settings as $key => $val) {
			if (is_array($val) && $subPath = $this->find_key($name, $val, $strict, $path)) {
				$path = array_merge($path, array($key), $subPath);
				return $path;
			} elseif ((!$strict && $key == $name) || ($strict && $key === $name)) {
				$path[] = $key;
				return $path;
			}
		}
		return false;
	}

}
