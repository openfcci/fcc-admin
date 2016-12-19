<?php
/*
Plugin Name: FCC Admin
Description: This plugin adds various site admin options, functions and fixes.
Version: 1.16.05.16 (alt version, updated 10-31-16)
Author: FCC Digital / Ryan Veitch
Author http://forumcomm.com/
License: GPLv2
*/

/*------------------------------------------------------------------------------
>>> TABLE OF CONTENTS:
--------------------------------------------------------------------------------
# Print FCC Help Commands
# Fast Spam Removal
# Cache Flushing: Flush Site Cache (Memcached) & Rewrite Rules
# Cache Flushing: Network Wide
# Available Shortcodes: Add Dropdown List Selector to TinyMCE Post Editor
# Hide MemcacheD object-cache.php error admin nag
------------------------------------------------------------------------------*/


/**
 * Print FCC Help Commands
 *
 * @since 1.16.05.16
 * @version 1.16.05.16
 */
function fcc_help() {
	echo 'Permanently delete comments marked as "span" or "trash": fcc_kill_spam();' . '<br>';
	echo 'Cache Flusing (Single Site): fcc_flush_cache();, fcc_flush();, flush_it_all();' . '<br>';
	echo 'Cache Flusing (Network Wide): fcc_flush_network()' . '<br>';
}

/*--------------------------------------------------------------
# Fast Spam Removal
--------------------------------------------------------------*/
/**
 * Permanently delete comments marked as "span" or "trash":
 *
 * @since 1.15.11.23
 * @version 1.15.11.23
 */
function fcc_kill_spam() {
	global $wpdb;
	$spam_comments_id_arr = $wpdb->get_col( "SELECT comment_id FROM {$wpdb->comments} WHERE comment_approved='spam' OR comment_approved='trash'" );
	if ( ! empty( $spam_comments_id_arr ) ) {
		$spam_comments_ids = implode( ', ', array_map('intval', $spam_comments_id_arr) );
		$wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_id IN ( $spam_comments_ids )");
		$wpdb->query("DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ( $spam_comments_ids )");
		$wpdb->query( "OPTIMIZE TABLE $wpdb->comments" );
		$wpdb->query( "OPTIMIZE TABLE $wpdb->commentmeta" );
	}
	if ( is_admin() ) echo 'complete';
}

/*--------------------------------------------------------------
# Cache Flushing
--------------------------------------------------------------*/
/**
 * Flush Site Cache (Memcached) & Rewrite Rules
 *
 * @since 1.16.05.16
 * @version 1.16.05.16
 */
function fcc_flush_cache() {
	global $wp_object_cache;
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
	flush_rewrite_rules();
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_flush();
	refresh_blog_details();
	echo 'Flushed all the things!';
}

# Function Wrapper for fcc_flush_cache()
function fcc_flush() {
	fcc_flush_cache();
}

# Function Wrapper fcc_flush_cache()
function flush_it_all() {
	fcc_flush_cache();
}

/**
 * Cache Flushing: Network Wide
 *
 * @since 1.16.05.16
 * @version 1.16.05.16
 */
function fcc_flush_network() {
	$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE public = '1'" );
	$cnt = count( $blogs );
	if ( ! empty( $blogs ) ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog );
			fcc_flush_cache();
			restore_current_blog();
		}
		echo 'Flushed all the things on ' . $cnt . ' public network sites.';
	}
}

/**
 * Available Shortcodes: Add Dropdown List Selector to TinyMCE Post Editor
 * Documentation: http://wpsnipp.com/index.php/functions-php/update-automatically-create-media_buttons-for-shortcode-selection/#
 * Added 11/23/15
 * Updated 10/31/16
 */
function add_sc_select() {
	global $post;
	if ( 'post' == $post->post_type ) {
		global $shortcode_tags;

		/* enter names of shortcode to exclude below: */
		$exclude = array(
	    'wp_caption',
	    'embed',
	    'globalrecentposts',
	    'fcc_jw_player',
	    'shortcake_dev',
	    'wangguard_reg',
	    'wangguardcontact',
	    'zilla_likes',
		);

		echo '&nbsp;<select id="sc_select"><option>Available Shortcodes</option>';
		foreach ( $shortcode_tags as $key => $val ) {
			if ( ! in_array( $key,$exclude ) ) {
				$shortcodes_list .= '<option value="['.$key.'][/'.$key.']">'.$key.'</option>';
			}
		}
		echo $shortcodes_list;
		echo '</select>';
	}
}
add_action( 'media_buttons','add_sc_select', 11 );


function fcc_sc_button_js() {
	global $post;
	if ( 'post' == $post->post_type ) {
		echo '<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery("#sc_select").change(function() {
							send_to_editor(jQuery("#sc_select :selected").val());
							return false;
						});
					});
					</script>';
	}
}
add_action( 'admin_head-post.php', 'fcc_sc_button_js' );
add_action( 'admin_head-post-new.php', 'fcc_sc_button_js' );

/*
* Hide Goodlayers/Simple Article Theme plugin banner messages from WP Admin Dashboard
* Added 10/03/15
*/
if ( is_admin() ) {
  function hide_goodlayers_plugin_notifications(){
         //if ( is_admin() ) {
             echo '
                 <style type="text/css">
                   div#setting-error-tgmpa {
                       display:none;
                   }
                 </style>
             ';
         //}
  }
  add_action('admin_head', 'hide_goodlayers_plugin_notifications');
}

/*
* Hide MemcacheD object-cache.php error admin nag
* Added 10/12/15
*/
if ( is_admin() ) {
  add_action( 'init' , 'fcc_remove_memcached_nag' );
  function fcc_remove_memcached_nag() {
    $option = get_option( 'wordpress_memcached_support_notice' );
    if ( $option = 'ERROR: could not create configured object-cache.php for your site, aborting' ) {
      remove_action( 'admin_notices', 'wordpress_memcached_support_show_admin_notice' );
    }
  }
}

/*
* Prevent MemcacheD support check from running
* Added 03/21/16
*/
if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	remove_action( 'admin_init', 'wordpress_memcached_support_check_for_update' );
}


/*--------------------------------------------------------------
# Adds 'FCC JW API' network settings page
--------------------------------------------------------------*/

/**
 * Create settings menu under tools
 * @since 1.16.10.05
 * @version 1.16.10.05
 */
function fcc_admin_create_settings_menu() {
	add_submenu_page(
		'settings.php',
		'JW API Settings',
		'JW API Settings',
		'manage_network',
		'fcc-podcast-settings',
		'fcc_jw_api_site_options_page'
	);
};
add_action( 'network_admin_menu', 'fcc_admin_create_settings_menu' );


/**
 * Options Page HTML
 * @since 1.16.10.05
 * @version 1.16.10.05
 */
function fcc_jw_api_site_options_page() {
	if ( is_multisite() && current_user_can( 'manage_network' ) ) {
		if ( isset( $_POST['action'] ) && 'update_jw_api_settings' == $_POST['action'] ) {

			// Store option values in a variable
			$jw_api_key = sanitize_text_field( $_POST['jw_api_key'] );
			$jw_api_secret = sanitize_text_field( $_POST['jw_api_secret'] );

			// Save option values
			update_site_option( 'jw_api_key', $jw_api_key );
			update_site_option( 'jw_api_secret', $jw_api_secret );

			// Just assume it all went according to plan
			echo '<div id="message" class="updated fade"><p><strong>JW API Settings Updated!</strong></p></div>';

		} // END if POST
		?>
		<div class="wrap">
		<h1>JW API Settings</h1>

		<form method="post">
				<input type="hidden" name="action" value="update_jw_api_settings" />
		    <table class="form-table">
		        <tr valign="top">
		        <th scope="row">JW API Key</th>
		        <td><input type="text" name="jw_api_key" value="<?php echo esc_attr( get_site_option('jw_api_key') ); ?>" /></td>
		        </tr>

		        <tr valign="top">
		        <th scope="row">JW API Secret</th>
		        <td><input type="text" name="jw_api_secret" value="<?php echo esc_attr( get_site_option('jw_api_secret') ); ?>" /></td>
		        </tr>
		    </table>

				<input type="submit" class="button-primary" name="update_jw_api_settings" value="Save Settings" />
		</form>
		<?php
	}
}
