<?php

Class KwikMeta{
    private $fields;
    static $field_group;

    public function __construct()
    {

    }

    public static function get_meta_array($post_id, $key) {
        $meta_array = get_post_meta($post_id, $key, false);
        return ( is_array($meta_array) && isset($meta_array[0]) ) ? $meta_array[0] : $meta_array;
    }

    public function get_fields($post, $field_group)
    {
        $field_vals = self::get_meta_array($post->ID, $field_group);
        $fields = get_transient($field_group);
        $flat_fields = $this->flattened_field_array($fields);
        $flat_field_vals = $this->merge_vals($flat_fields, $field_vals);

        return $this->generate_fields($flat_field_vals, $field_group);
    }

    private function merge_vals($fields, $field_vals)
    {
        foreach ($fields as $key => $value) {
            $fields[$key]['value'] = isset($field_vals[$key]) ? $field_vals[$key] : $fields[$key]['value'];
        }
        return $fields;
    }

    public function flattened_field_array($fields, &$fields_array = array()){
        foreach ($fields as $key => $value) {
            if(isset($fields[$key]['fields'])){
                $this->flattened_field_array($fields[$key]['fields'], $fields_array);
            } else{
                $fields_array[$key] = $fields[$key];
            }
        }
        return $fields_array;
    }

    public function generate_fields($fields, $field_group)
    {
        $inputs = new KwikInputs();
        $output = $inputs->nonce($field_group . '_nonce', wp_create_nonce(plugin_basename(__FILE__)));

        foreach ($fields as $key => $value) {
            $name = $field_group.'['.$key.']';
            $field_options = isset($fields[$key]['options']) ? $fields[$key]['options'] : null;
            $field_attrs = isset($fields[$key]['attrs']) ? $fields[$key]['attrs'] : null;
            $output .= $inputs->$fields[$key]['type']($name, $fields[$key]['value'], $fields[$key]['title'], $field_attrs, $field_options);
        }

        return $output;
    }

    /**
     * builds a values array for only fields set with the transient
     * @param  [object] $post        Post object to the meta is for
     * @param  [string] $field_group namespace this meta data belongs to
     */
    public function save_meta($the_post, $field_group)
    {
        $field_vals = array();
        $fields = get_transient($field_group);
        $post = $_POST;
        foreach ($fields as $key => $value) {
            $field_vals[$key] = isset($post[$field_group][$key]) ? $post[$field_group][$key] : $fields[$key]['value'];
        }

        if (!wp_verify_nonce($post[$field_group.'_nonce'], plugin_basename(__FILE__))) {
            return $the_post->ID;
        }

        $this->update_meta($the_post->ID, $field_group, $field_vals);
    }

    /**
     * add, update or delete post meta
     * @param  [Number] $post_id  eg. 123
     * @param  [String] $field_name key of the custom field to be updated
     * @param  string $value
     */
    public static function update_meta($post_id, $field_name, $value = '')
    {
        if (empty($value) || !$value) {
            delete_post_meta($post_id, $field_name);
        } else {
            update_post_meta($post_id, $field_name, $value);
        }
    }

}
