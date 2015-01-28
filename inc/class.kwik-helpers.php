<?php

class KwikHelpers
{

    public static function positions()
    {
        $t = 'Top';
        $r = 'Right';
        $b = 'Bottom';
        $l = 'Left';
        $m = 'Middle';
        $c = 'Center';
        $z = '0';
        $s = ' ';
        $f = '50%';
        $o = '100%';
        $positions = array(
            $z . $s . $z => $t . $s . $l, $z . $s . $f => $t . $s . $c, $z . $s . $o => $t . $s . $r, $f . $s . $z => $m . $s . $l,
            $f . $s . $f => $m . $s . $c, $f . $s . $o => $m . $s . $r, $o . $s . $z => $b . $s . $l, $o . $s . $f => $b . $s . $c,
            $o . $s . $o => $b . $s . $r,
        );
        return $positions;
    }

    public static function repeat()
    {
        $R = 'Repeat';
        $r = strtolower($R);
        return array(
            'no-' . $r => 'No ' . $R,
            $r => $R,
            $r . '-x' => $R . '-X',
            $r . '-y' => $R . '-Y',
        );
    }

    public static function target()
    {
        $target = array(
            '_blank' => __('New Window/Tab', 'kwik'),
            '_self' => __('Same Window', 'kwik'),
        );
        return $target;
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
            (object) array('family' => '“Helvetica Neue”'),
            (object) array('family' => '“Baskerville Old Face”'),
            (object) array('family' => '“Trebuchet MS”'),
            (object) array('family' => '"Century Gothic"'),
            (object) array('family' => '“Courier Bold"'),
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
}
