<?php

class KwikHelpers
{

    public static function positions()
    {
        return array(
            '0 0' => 'Top Left',
            '0 50%' => 'Top Center',
            '0 100%' => 'Top Right',
            '50% 0' => 'Middle Left',
            '50% 50%' => 'Middle Center',
            '50% 100%' => 'Middle Right',
            '100% 0' => 'Bottom Left',
            '100% 50%' => 'Bottom Center',
            '100% 100%' => 'Bottom Right'
        );
    }

    public static function repeat()
    {
        return array(
            'no-repeat' => 'No Repeat',
            'repeat' => 'Repeat',
            'repeat-x' => 'Repeat-X',
            'repeat-y' => 'Repeat-Y'
        );
    }

    public static function target()
    {
        return array(
            '_blank' => __('New Window/Tab', 'kwik'),
            '_self' => __('Same Window', 'kwik'),
        );
    }

    public static function bg_size()
    {
        $bg_size = array(
            'auto' => __('Default', 'kwik'),
            '100% 100%' => __('Stretch', 'kwik'),
            'cover' => __('Cover', 'kwik'),
        );
        return $bg_size;
    }

    public static function bg_attachment()
    {
        $bg_attachment = array(
            'scroll' => __('Scroll', 'kwik'),
            'fixed' => __('Fixed', 'kwik'),
        );
        return $bg_attachment;
    }

    public static function font_weights()
    {
        $font_weights = array(
            'normal' => __('Normal', 'kwik'),
            'bold' => __('Bold', 'kwik'),
            'bolder' => __('Bolder', 'kwik'),
            'lighter' => __('Lighter', 'kwik'),
        );
        return $font_weights;
    }

    public static function default_fonts()
    {
        $font_weights = array(
            (object) array('family' => '"Helvetica Neue"'),
            (object) array('family' => '"Baskerville Old Face"'),
            (object) array('family' => '"Trebuchet MS"'),
            (object) array('family' => '"Century Gothic"'),
            (object) array('family' => '"Courier Bold"'),
        );
        return $font_weights;
    }

    public static function order_by()
    {
        $order_by = array(
            'menu_order' => __('Menu Order', 'kwik'),
            'post_title' => __('Alphabetical', 'kwik'),
            'post_date' => __('Post Date', 'kwik'),
        );
        return $order_by;
    }

    public static function order()
    {
        $order = array(
            'ASC' => __('Ascending', 'kwik'),
            'DESC' => __('Descending', 'kwik'),
        );
        return $order;
    }

    public static function css_map($val, $key)
    {

        switch ($key) {
        case 'Underlined':
            $key = 'text-decoration';
            break;

        case 'Bold':
            $key = 'font-weight';
            break;

        case 'Italic':
            $key = 'font-style';
            break;

        default:
            $key = $key;
            break;
        }
    }


}
