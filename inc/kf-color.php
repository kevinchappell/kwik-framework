<?php

Class KwikColors{

  public function hex_to_RGB($hex_color) {

    $rgb = array();
    $rgb['red'] = hexdec(substr($hex_color, 1, 2));
    $rgb['green'] = hexdec(substr($hex_color, 3, 2));
    $rgb['blue'] = hexdec(substr($hex_color, 5, 2));

    return $rgb;
  }

  public function image_color_allocate_from_hex($img, $hexstr) {
    $int = hexdec($hexstr);

    return ImageColorAllocate($img,
      0xFF&($int >> 0x10),
      0xFF&($int >> 0x8),
      0xFF&$int);
  }

  public function invert_color($start_color) {

    $color_red = hexdec(substr($start_color, 1, 2));
    $color_green = hexdec(substr($start_color, 3, 2));
    $color_blue = hexdec(substr($start_color, 5, 2));

    $new_red = dechex(255 - $color_red);
    $new_green = dechex(255 - $color_green);
    $new_blue = dechex(255 - $color_blue);

    if (strlen($new_red) == 1) {$new_red .= '0';}
    if (strlen($new_green) == 1) {$new_green .= '0';}
    if (strlen($new_blue) == 1) {$new_blue .= '0';}

    $new_color = '#' . $new_red . $new_green . $new_blue;

    return $new_color;
  }

}
