<?php

/**
 * Create hashtag taxonomy
 *
 * @return void
 */
function hashbuddy_create_hashtag_taxonomies() {

	$args = array(
		'label'             => __( 'Hashtag' ),
		'public'            => false,
		'rewrite'           => false,
	);

	register_taxonomy( 'hashtag', null, $args );

}
add_action( 'init', 'hashbuddy_create_hashtag_taxonomies', 0 );

/**
 * Add or update hashtag term count
 *
 * @param  array $hashtags
 * @return void
 */
function hashbuddy_update_terms( $hashtags ) {
	global $wpdb;

	foreach ( $hashtags as $hashtag ) {

		$count = 0;
		$term = get_term_by( 'name', $hashtag, 'hashtag' );
		$term_id = $term ? $term->term_id : false;

		if ( ! $term ) {
			$new_term = wp_insert_term( $hashtag, 'hashtag' );
			$term_id = $new_term['term_id'];
			$count = 1;
		} else {
			$cnt = (int) $term->count;
			$count = ( $cnt + 1 );
		}

		if ( 0 !== $count && $term_id ) {
			$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term_id ) );
		}
	}

	delete_transient( 'hashbuddy_hashtags' );

}

/**
 * Total hashtag count
 *
 * @return integer
 */
function hashbuddy_total_hashtag_count() {
	global $wpdb;

	$results = $wpdb->get_results( "SELECT SUM(count) AS total_count FROM $wpdb->term_taxonomy WHERE taxonomy = 'hashtag'" );

	return $results[0]->total_count;
}

/**
 * Hashtag counts
 *
 * @return array
 */
function hashbuddy_get_hashtags() {
	global $wpdb;

	$results = array();

	if ( false !== ( $hashtag_cache = get_transient( 'hashbuddy_hashtags' ) ) ) {
		$results = $hashtag_cache;
	} else {

		$terms = get_terms( array(
			'taxonomy' => 'hashtag',
			'hide_empty' => false,
		) );

		foreach ( $terms as $term ) {
			$results[ $term->name ] = (int) $term->count;
		}

		arsort( $results );

		$results = array(
			'terms' => $results,
			'total_count' => hashbuddy_total_hashtag_count(),
		);

		set_transient( 'hashbuddy_hashtags', $results, YEAR_IN_SECONDS );

	}

	$results = apply_filters( 'hashbuddy_get_hashtags', $results );

	return $results;
}
