<?php

Class KwikSettings{

    public function __construct()
    {

    }

    public static function generate_fields($fields)
    {

        foreach ($fields as $key => $value) {
            # code...
        }

    }

    public static function get_meta_array($post_id, $key) {
        $meta_array = get_post_meta($post_id, $key, false);
        var_dump($meta_array);
        return $meta_array[0];
    }

    public function register_fields($field_group, $fields)
    {
        $this->fields[$field_group] = $fields;
    }

    public static function get_fields($field_group)
    {
        return $this->fields[$field_group];
    }

}
