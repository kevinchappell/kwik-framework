<?php

/**
 * Plugin Name: Kwik Framework
 * Plugin URI: http://kevinchappell.github.io/kwik-framework/
 * Description: Reusable utilities and inputs to aid in WordPress theme and plugin creation
 * Author: Kevin Chappell
 * Version: 0.5.7
 * Author URI: http://kevin-chappell.com
 */

if ( ! class_exists( 'KwikUtils' ) ) {
	define( 'KF_BASENAME', basename( dirname( __FILE__ ) ) );
	define( 'KF_FUNC', preg_replace( '/-/', '_', KF_BASENAME ) );
	define( 'KF_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
	define( 'KF_PATH', untrailingslashit( dirname( __FILE__ ) ) );
	define( 'KF_CACHE', trailingslashit( dirname( __FILE__ ) ) . 'cache' );
	define( 'KF_PREFIX', 'kf_' );

	foreach ( glob( KF_PATH . '/inc/*.php' ) as $inc_filename ) {
		include $inc_filename;
	}

	foreach ( glob( KF_PATH . '/components/*.php' ) as $component_filename ) {
		include $component_filename;
	}

	// Load Widgets
	foreach ( glob( KF_PATH . '/widgets/*.php' ) as $widget_filename ) {
		include $widget_filename;
	}

	if ( ! file_exists( KF_CACHE ) ) {
		mkdir( KF_CACHE, 0755, true );
	}

	/**
	 * Enqueues scripts and styles for admin screens
	 * @category scripts_and_styles
	 * @since KwikFramework .1
	 */
	function kf_admin_js_css( $hook ) {

		wp_enqueue_style( 'kwik-framework-resource', KF_URL . '/css/' . KF_PREFIX . 'resource.css', false, '2014-10-28' );
		wp_enqueue_style( 'kwik-framework-admin', KF_URL . '/css/' . KF_PREFIX . 'admin.css', false, '2014-10-28' );
		wp_enqueue_script( 'kwik-framework-admin', KF_URL . '/js/' . KF_PREFIX . 'admin.js', array( 'jquery' ), null, true );
	}
	add_action( 'admin_enqueue_scripts', 'kf_admin_js_css' );

	function kf_scripts() {
		wp_enqueue_style( 'kf-style', KF_URL . '/css/' . KF_PREFIX . 'style.css' );
	}

	add_action( 'wp_enqueue_scripts', 'kf_scripts' );

}
