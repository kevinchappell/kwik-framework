<?php

/**
* Name: Kwik Framework
* URI: http://kevin-chappell.com/kwik-framework
* Description: Reusable utilities and inputs to aid in WordPress theme and plugin creation
* Author: Kevin Chappell
* Version: .1
* Author URI: http://kevin-chappell.com
*/


if (!class_exists('KwikUtils')) {

  define( 'KF_BASENAME',  basename(dirname( __FILE__ )));
  define( 'KF_URL',       get_template_directory_uri()."/inc/".KF_BASENAME);
  define( 'KF_PATH',      untrailingslashit( dirname( __FILE__ ) ) );
  define( 'KF_CACHE',     trailingslashit( dirname( __FILE__ ) )."cache" );

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
    wp_enqueue_script( 'kf_admin',  KF_URL . '/js/'.KF_BASENAME.'.js', array('jquery'), NULL, true );
  }
  add_action( 'admin_enqueue_scripts', 'kf_admin_js_css');


}
