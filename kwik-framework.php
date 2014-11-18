<?php

/**
* Plugin Name: Kwik Framework
* Plugin URI: http://kevin-chappell.com/kwik-framework
* Description: Reusable utilities and inputs to aid in WordPress theme and plugin creation
* Author: Kevin Chappell
* Version: .1
* Author URI: http://kevin-chappell.com
*/


if (!class_exists('KwikUtils')) {

  define( 'KF_BASENAME',  basename(dirname( __FILE__ )));
  define( 'KF_FUNC',      preg_replace('/-/', '_', KF_BASENAME));
  define( 'KF_URL',       untrailingslashit(plugins_url('', __FILE__)));
  define( 'KF_PATH',      untrailingslashit( dirname( __FILE__ ) ) );
  define( 'KF_CACHE',     trailingslashit( dirname( __FILE__ ) )."cache" );
  define( 'KF_PREFIX',    'kf_' );

  foreach (glob(KF_PATH . "/inc/*.php") as $inc_filename) {
    include $inc_filename;
  }

  if (!file_exists(KF_CACHE)) {
    mkdir(KF_CACHE, 0755, true);
  }


	/**
	 * Enqueues scripts and styles for admin screens
	 *
	 * @since KwikFramework .1
	 */
  function kf_admin_js_css($hook) {
    wp_enqueue_style('kf_resource_css', KF_URL . '/css/' . KF_PREFIX . 'resource.css', false, '2014-10-28');
    wp_enqueue_style('kf_admin_css', KF_URL . '/css/' . KF_PREFIX . 'admin.css', false, '2014-10-28');
    wp_enqueue_script( 'kf_admin_js',  KF_URL . '/js/'.KF_PREFIX. 'admin.js', array('jquery'), NULL, true );
  }
  add_action( 'admin_enqueue_scripts', 'kf_admin_js_css');


}
