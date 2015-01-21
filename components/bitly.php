<?php

/**
 * Released under GPL
 * This is a simple class that gives you options to use bitly's API method
 * /v3/expand and /v3/shorten
 * @access public
 * @author Jaspreet Chahal
 * @copyright Jaspreet Chahal
 * @version 0.1a
 * @link http://jaspreetchahal.org
 * @package bitly
 */

class Bitly
{
    private $_username = null;
    private $_apikey = null;
    private $_format = null;
    // format can be json,xml,txt
    // read more here http://dev.bitly.com/formats.html
    private $_apiurl = null;
    public function __construct($username, $apikey, $secure = false, $format = 'txt')
    {
        $this->_username = $username;
        $this->_apikey = $apikey;
        $this->_format = $format;
        if ($secure) {
            $this->_apiurl = 'https://api-ssl.bitly.com';
        } else {
            $this->_apiurl = 'http://api.bitly.com';
        }
    }

    /*
    Read more: http://dev.bitly.com/links.html#v3_shorten
     */
    public function shortenURL($urltoshorten)
    {
        // check here if the URL is valid or not
        $bitlyconnector = $this->_apiurl . '/v3/shorten?login=' . $this->_username . '&apiKey=' . $this->_apikey . '&uri=' . urlencode($urltoshorten) . '&format=' . $this->_format;
        return curl_get_result($bitlyconnector);
    }

    /*
    Read more: http://dev.bitly.com/links.html#v3_expand
     */
    public function lengthenURL($urltolongify)
    {
        $bitlyconnector = $this->_apiurl . '/v3/expand?login=' . $this->_username . '&apiKey=' . $this->_apikey . '&shortUrl=' . urlencode($urltolongify) . '&format=' . $this->_format;
        return curl_get_result($bitlyconnector);
    }

}

// function social_link()
// {
//     $options = KwikThemeOptions::kt_get_options();
//     if (!empty($options['bitly'][0])) {
//         bitly();
//     } else {
//         current_page_url();
//     }
// }

// function bitly()
// {
//     global $post;
//     $options = KwikThemeOptions::kt_get_options();
//     $bitly_meta = get_post_meta($post->ID, 'bitly_meta', true);

//     if (is_single() && $bitly_meta) {
//         return urldecode($bitly_meta);
//     } else {

//         $bitly = new Bitly($options['bitly'][0], $options['bitly'][1]);
//         $bitly_short_url = $bitly->shortenURL(current_page_url());
//         if (is_single() && !$bitly_meta) {
//             add_post_meta($post->ID, 'bitly_meta', urlencode($bitly_short_url), true);
//         }

//         return $bitly_short_url;
//     }

// }

// // Save Bitly meta data
// function save_bitly_urls($post_id, $post)
// {

//     // Is the user allowed to edit the post or page?
//     if (!current_user_can('edit_post', $post->ID)) {
//         return $post->ID;
//     }

//     $options = KwikThemeOptions::kt_get_options();
//     if (!empty($options['bitly'][0]) && !empty($options['bitly'][1])) {
//         $bitly = new Bitly($options['bitly'][0], $options['bitly'][1]);
//         $bitly_short_url = $bitly->shortenURL(get_permalink($post_id));

//         if ($post->post_type == 'revision') {
//             return;
//         }

//         __update_post_meta($post->ID, 'bitly_meta', urlencode($bitly_short_url));
//     } else {
//         return;
//     }

// }
//add_action('save_post', 'save_bitly_urls', 1, 2);
