<?php

/**
 * Ajax utilities for autocomplete and other niceties in the Kwik Framework
 */
class KwikAjax {

	public function __construct() {
		add_action( 'wp_ajax_kf_query_cpt', array( $this, 'kf_query_cpt' ) );
	}

	function kf_query_cpt(){
		$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );
		$input = filter_input( INPUT_POST, 'term', FILTER_SANITIZE_STRING );
		if ( empty($post_type) || empty($post_type) ){
			return;
		}
		global $wpdb;
		$data = array();
		$table_name = $wpdb->prefix.'posts';

		$query = "
		SELECT concat( post_title ) name, 1 cnt, ID as the_id FROM ".$table_name." t
		WHERE post_status='publish'
		AND post_type = '".$post_type."'
		AND post_date < NOW()
		AND post_title LIKE '%$input%'
		ORDER BY post_title
		LIMIT 10
		";

		$query_results = mysql_query( $query );

		while ( $row = mysql_fetch_array( $query_results ) ) {
			$json = array();
			$json['label'] = $row['name'];
			$json['id'] = $row['the_id'];
			$json['image'] = get_the_post_thumbnail($row['the_id'], 'thumbnail', array('class' => 'kf_prev_img'));
			$data[] = $json;
		}

		header( 'Content-type: application/json' );
		echo json_encode( $data );
		wp_die();
	}

}

new KwikAjax();
