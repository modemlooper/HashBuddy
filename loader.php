<?php
/*
Plugin Name: HashBuddy
Plugin URI: http://modemlooper.me
Description: Hashtags for WordPress, BuddyPress and bbPress
Version: 1.6.0
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: modemlooper
Author URI: http://twitter.com/modemlooper
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HASHBUDDY_URL', plugin_dir_url( __FILE__ ) );

function hashbuddy_init() {

	// Load cmb2.
	if ( file_exists( __DIR__ . '/vendors/cmb2/init.php' ) ) {
		require_once  __DIR__ . '/vendors/cmb2/init.php';
	} elseif ( file_exists( __DIR__ . '/vendors/CMB2/init.php' ) ) {
		require_once  __DIR__ . '/vendors/CMB2/init.php';
	}

	require( dirname( __FILE__ ) . '/includes/taxonomy.php' );
	require( dirname( __FILE__ ) . '/includes/hashbuddy.php' );
	require( dirname( __FILE__ ) . '/includes/widgets.php' );
	require( dirname( __FILE__ ) . '/includes/admin.php' );
	require( dirname( __FILE__ ) . '/includes/tools.php' );

}
add_action( 'bp_loaded', 'hashbuddy_init' );

function hashbuddy_bbp_hashtags_init() {

	add_filter( 'bbp_new_topic_pre_content', 'hashbuddy_bbpress_hashtags_filter' );
	add_filter( 'bbp_edit_topic_pre_content', 'hashbuddy_bbpress_hashtags_filter' );
	add_filter( 'bbp_new_reply_pre_content', 'hashbuddy_bbpress_hashtags_filter' );
	add_filter( 'bbp_edit_reply_pre_content', 'hashbuddy_bbpress_hashtags_filter' );

}
add_action( 'wp', 'hashbuddy_bbp_hashtags_init', 88 );


function hashbuddy_activity_hashtags_init() {

	if ( ! bp_is_active( 'activity' ) )
		return;

	// need to check this as BP uses same filter for add and get comment 
	// https://buddypress.trac.wordpress.org/ticket/5079
	if ( isset( $_POST['action'] ) && 'new_activity_comment' === $_POST['action'] ) {
		add_filter( 'bp_activity_comment_content', 'hashbuddy_activity_hashtags_filter' );
	}

	add_filter( 'bp_activity_new_update_content', 'hashbuddy_activity_hashtags_filter' );
	add_filter( 'groups_activity_new_update_content', 'hashbuddy_activity_hashtags_filter' );

	add_filter( 'bp_blogs_activity_new_post_content', 'hashbuddy_activity_hashtags_filter' );
	add_filter( 'bp_blogs_activity_new_comment_content', 'hashbuddy_activity_hashtags_filter' );

	//support edit activity stream plugin
	add_filter( 'bp_edit_activity_action_edit_content', 'hashbuddy_activity_hashtags_filter' );

}
add_action( 'bp_include', 'hashbuddy_activity_hashtags_init', 88 );
