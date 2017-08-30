<?php

if ( ! defined( 'ABSPATH' ) ) { exit;
}

/**
 * Swap hashtags in content with links to search
 *
 * @param  string $content
 * @return string modified content
 */
function hashbuddy_activity_hashtags_filter( $content ) {
	global $bp;

	$pattern = '/[#]([\p{L}_0-9a-zA-Z-]+)/iu';

	$activity_url = trailingslashit( get_bloginfo( 'url' ) ) . BP_ACTIVITY_SLUG;

	preg_match_all( ' ' . $pattern . ' ', $content, $hashtags );

	if ( $hashtags ) {
		/* Make sure there's only one instance of each tag */
		if ( ! $hashtags = array_unique( $hashtags[1] ) ) {
			return $content;
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
			}
		}

		hashbuddy_update_terms( $hashtags );
	}

	return $content;
}

/**
 * Filter search where condition if string is a hashtag
 *
 * @param  array  $where_conditions
 * @param  object $r
 * @param  array  $select_sql
 * @param  array  $from_sql
 * @param  array  $join_sql
 * @return array
 */
function hashbuddy_bp_activity_get_where_conditions( $where_conditions, $r, $select_sql, $from_sql, $join_sql ) {

	if ( isset( $r['search_terms'] ) && ! empty( $r['search_terms'] ) ) {

		$hash = sanitize_text_field( $r['search_terms'] );

		$tag = mb_substr( $hash, 0, 1 );

		if ( '#' === $tag ) {
			$where_conditions['search_sql'] = "a.content LIKE '%>" . $hash . "<%'";
		}
	}

	return $where_conditions;

}
add_filter( 'bp_activity_get_where_conditions', 'hashbuddy_bp_activity_get_where_conditions', 10, 5 );

/**
 * Turn on stream mode when searching a hashtag so comment show
 *
 * @param  array $retval
 * @return array
 */
function hashbuddy_stream( $retval ) {

	if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {

		$hash = sanitize_text_field( $_GET['s'] );

		$tag = mb_substr( $hash, 0, 1 );

		if ( '#' === $tag ) {
			$retval['display_comments'] = 'stream';
		}
	}

	return $retval;
}
add_filter( 'bp_after_has_activities_parse_args', 'hashbuddy_stream' );

/**
 * Filter topics by hashtag
 *
 * @param  string $content
 * @return string modified content
 */
function hashbuddy_bbpress_hashtags_filter( $content ) {
	global $bp;

	$pattern = '/[#]([\p{L}_0-9a-zA-Z-]+)/iu';

	$activity_url = trailingslashit( get_bloginfo( 'url' ) ) . BP_ACTIVITY_SLUG;

	preg_match_all( ' ' . $pattern . ' ', $content, $hashtags );

	if ( $hashtags ) {
		/* Make sure there's only one instance of each tag */
		if ( ! $hashtags = array_unique( $hashtags[1] ) ) {
			return $content;
		}

		foreach ( (array) $hashtags as $hashtag ) {

			$hashtag = apply_filters( 'hashtag_filter', $hashtag );

			if ( false !== $hashtag ) {

				$pattern = "/(^|\s|\b)#" . $hashtag . "($|\b)/";
				$link = ' <a href="' . $activity_url . '/?s=%23' . htmlspecialchars( $hashtag ) . '" rel="nofollow" class="hashtag" id="' . htmlspecialchars( $hashtag ) . '">#' . htmlspecialchars( $hashtag ) . '</a>';
				$url = apply_filters( 'hashtag_bbpress_link', $link, $activity_url, $hashtag );

				$content = preg_replace( $pattern, $url, $content );
			} else {
				unset( $hashtags[ $hashtag ] );
			}
		}

		hashbuddy_update_terms( $hashtags );
	}

	return $content;
}

/**
 * Updates hash term cout on activity delete
 *
 * @param  integer $activity_id
 * @param  integer $user_id
 * @return void
 */
function hashbuddy_delete_activity( $activity_id, $user_id ) {
	global $wpdb;

	// Load up the activity item.
	$activity = new BP_Activity_Activity( $activity_id );

	if ( ! $activity ) {
		return;
	}

	$pattern = '/[#]([\p{L}_0-9a-zA-Z-]+)/iu';

	preg_match_all( $pattern, $activity->content, $hashtags );

	if ( $hashtags ) {

		 $hashtag_tax = hashbuddy_get_hashtags();

		/* Make sure there's only one instance of each tag */
		if ( ! $hashtags = array_unique( $hashtags[1] ) ) {
			return;
		}

		foreach ( (array) $hashtags as $hashtag ) {

			if ( array_key_exists( $hashtag, $hashtag_tax['terms'] ) ) {

				$term = get_term_by( 'name', $hashtag, 'hashtag' );

				$count = $hashtag_tax['terms'][ $hashtag ];

				if ( 0 !== $count ) {
					$count = $count -1;
					$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term->term_id ) );
				}
			}
		}

		delete_transient( 'hashbuddy_hashtags' );

	}
}
add_action( 'bp_activity_before_action_delete_activity', 'hashbuddy_delete_activity', 10, 2 );
