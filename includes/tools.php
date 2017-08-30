<?php

/**
 * CMB2 sanitize callback. Edits activity comment. Returns false so check isnt saved.
 *
 * @return boolean
 */
function bp_admin_repair_hashtags() {
	global $wpdb;

	$bp = buddypress();

	$activities = $wpdb->get_results( "SELECT id FROM {$bp->activity->table_name} WHERE type = 'activity_update' OR type = 'activity_comment' OR type = 'bbp_topic_create' OR type = 'bbp_reply_create' AND content NOT LIKE '% class=\"hashtag\" %'" );

	if ( $activities ) {

		foreach ( $activities as $activity ) {

			add_filter( 'bp_bypass_check_for_moderation', '__return_true' );
			$old_activity = new BP_Activity_Activity( $activity->id );
			$hashed = hashbuddy_activity_hashtags_edit( $old_activity->content );
			if ( $hashed ) {
				$old_activity->content = $hashed;
				$old_activity->save();
			}
		}
	}

	return false;

}


/**
 * Swap hashtags in content with links to search
 *
 * @param  string $content
 * @return string|boolean modified  or false
 */
function hashbuddy_activity_hashtags_edit( $content ) {
	global $bp;

	$pattern = '/[#]([\p{L}_0-9a-zA-Z-]+)/iu';

	$activity_url = trailingslashit( get_bloginfo( 'url' ) ) . BP_ACTIVITY_SLUG;

	preg_match_all( ' ' . $pattern . ' ', $content, $hashtags );

	if ( $hashtags ) {
		/* Make sure there's only one instance of each tag */
		if ( ! $hashtags = array_unique( $hashtags[1] ) ) {
			return false;
		}

		foreach ( (array) $hashtags as $hashtag ) {

			$hashtag = apply_filters( 'hashtag_filter', $hashtag );

			if ( false !== $hashtag ) {

				$pattern = "/(^|\s|\b)#" . $hashtag . "($|\b)/";
				$hashtag = sanitize_text_field( $hashtag );

				$link = ' <a href="' . $activity_url . '/?s=%23' . htmlspecialchars( $hashtag ) . '" rel="nofollow" class="hashtag" id="' . htmlspecialchars( $hashtag ) . '">#' . htmlspecialchars( $hashtag ) . '</a>';
				$url = apply_filters( 'hashtag_activity_link', $link, $activity_url, $hashtag );

				$content = preg_replace( $pattern, $url, $content );

			} else {
				unset( $hashtags[ $hashtag ] );
				$content = false;
			}
		}

		hashbuddy_update_terms( $hashtags );

	}

	return $content;
}

/**
 * Recounts hashtags
 *
 * @return void
 */
function bp_admin_repair_hashtags_count() {
	global $wpdb;

	$bp = buddypress();

	delete_transient( 'hashbuddy_hashtags' );

	$wpdb->update( $wpdb->term_taxonomy, array( 'count' => 0 ), array( 'taxonomy' => 'hashtag' ) );

	$terms = get_terms( array(
		'taxonomy' => 'hashtag',
		'hide_empty' => false,
	) );

	foreach ( $terms as $term ) {
		$tag = '>#' . $term->name . '<';
		$activities = $wpdb->get_results( "SELECT id FROM {$bp->activity->table_name} WHERE ( type = 'activity_update' OR type = 'activity_comment' OR type = 'bbp_topic_create' OR type = 'bbp_reply_create' ) AND content LIKE '%" . $tag . "%'" );

		if ( $activities ) {
			$count = count( $activities );
			if ( 0 !== $count && $term->term_id ) {
				$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term->term_id ) );
			}
		}
	}
}
