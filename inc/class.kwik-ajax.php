<?php
namespace Kwik\Ajax;

/**
 * Ajax utilities for autocomplete and saving posts
 *
 * @package Kwik Framework
 * @since   0.5.6
 */

add_action( 'wp_ajax_kf_query_posts', __namespace__.'\kf_query_posts' );
add_action( 'wp_ajax_kf_save_post', __namespace__.'\kf_save_post' );
add_action( 'wp_ajax_kf_save_meta', __namespace__.'\kf_save_meta' );

function kf_query_posts() {
	$filtered_input = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

	$query_args = array(
	's' => $filtered_input['term'],
	'post_type' => 'any',
	);

	if ( isset( $filtered_input['post_type'] ) ) {
		$query_args['post_type'] = $filtered_input['post_type'];
	}

	$transient_id = str_replace( ' ', '-', implode( '-' , $query_args ) );
	if ( false === ( $kf_posts = get_transient( $transient_id ) ) ) {

		$kf_posts_query = new \WP_Query( $query_args );

		if ( $kf_posts_query->have_posts() ) {
			$kf_post = array();

			while ( $kf_posts_query->have_posts() ) :
				$kf_posts_query->the_post();
				$kf_post['label'] = get_the_title();
				$kf_post['id'] = get_the_ID();
				$kf_post['thumbnail'] = get_the_post_thumbnail( $kf_post['id'], 'thumbnail', array( 'class' => 'kf_prev_img' ) );
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $kf_post['id'] ), 'thumbnail' );
				$kf_post['thumbnail_src'] = $thumb['0'];
				$post_meta = get_post_meta( $kf_post['id'] );
				$kf_post['meta'] = array();
				foreach ( $post_meta as $key => $value ) {
					$kf_post['meta'][ $key ] = get_post_meta( $kf_post['id'], $key );
				}
				$kf_posts[] = $kf_post;
				endwhile;
		}

		wp_reset_postdata();

		set_transient( $transient_id, $kf_posts, HOUR_IN_SECONDS );
	}

	wp_send_json( $kf_posts );
}

/**
 * Ajax save utility for admin
 * @return [json] JSON response
 */
function kf_save_post() {

	$filtered_input = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
	$reponse = array(
			'success' => false,
			'error' => 'Failed security',
			);

	check_ajax_referer( $filtered_input['nonceKey'], 'nonceValue' );

	$args = array(
		'post' => $filtered_input['post'],
		'meta' => $filtered_input['meta'],
	);

	// Set the status to match the parent
	if ( isset( $args['post']['post_parent'] ) && ! isset( $args['post']['post_status'] ) ) {
		$args['post']['post_status'] = get_post_status( $args['post']['post_parent'] );
	}

	$post_id = wp_insert_post( $args['post'] );

	if ( is_wp_error( $post_id ) ) {
		$reponse = array(
			'success' => false,
			'error' => $post_id,
			);
		wp_send_json( $reponse );
	}

	// Set thumbnail if there is one
	if ( isset( $args['post']['thumbnail'] ) && ! empty( $args['post']['thumbnail'] ) ) {
		set_post_thumbnail( $post_id, $args['post']['thumbnail'] );
	}

	// save meta
	foreach ( $args['meta'] as $key => $value ) {
		\KwikMeta::update_meta( $post_id, $key, $value );
	}

	$reponse = array(
		'success' => true,
		'message' => 'Post updated',
		'postID'	=> $post_id,
	);

	wp_send_json( $reponse );
}

/**
 * Ajax save meta utility for admin
 * @return [json] JSON response
 */
function kf_save_meta() {

	// $args = array(
	// 	'post_id' => FILTER_SANITIZE_STRING,
	// 	'nonceKey' => FILTER_SANITIZE_STRING,
	// 	'nonceValue' => FILTER_SANITIZE_STRING,
	// 	'meta' => FILTER_REQUIRE_ARRAY,
	// );

	$filtered_input = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

	$reponse = array(
			'success' => false,
			'error' => 'Failed security',
			);

	check_ajax_referer( $filtered_input['nonceKey'], 'nonceValue' );

	$args = array(
		'post_id' => (int) $filtered_input['post_id'],
		'meta' => $filtered_input['meta'],
	);

	// save meta
	foreach ( $filtered_input['meta'] as $key => $value ) {
		\KwikMeta::update_meta( $args['post_id'], $key, $value );
	}

	$reponse = array(
		'success' => true,
		'message' => 'Post Meta Updated',
		'postID'	=> $args['post_id'],
	);

	wp_send_json( $reponse );
}
