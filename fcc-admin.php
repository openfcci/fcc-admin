<?php
/*
Plugin Name: FCC Admin
Description: Adds various site admin options, functions fixes and network settings.
Version: 1.16.10.05
Author: FCC Digital / Ryan Veitch
Author http://forumcomm.com/
License: GPLv2
Network: True
*/


function set_lastupdated_to_lastpostdate2( $wpdb_blogid ) {

  switch_to_blog( $wpdb_blogid );

  $lastpostdate = get_lastpostdate( 'blog' );

  // Update the last_updated time
  //update_blog_details( $wpdb_blogid, array('last_updated' => $lastpostdate ) );

  echo 'Last Post Date for site ' . $wpdb_blogid . ' updated to: ' . $lastpostdate . '<br>';
  echo 'Last Updated Date for site ' . $wpdb_blogid . ' is currently: ' . get_blog_details( $blogId )->last_updated;

  refresh_blog_details($wpdb_blogid);
  restore_current_blog();
}


//Function to set the last updated to the last post date in the sites table
function set_lastupdated_to_lastpostdate3( $wpdb_blogid ) {
  global $wpdb;

  switch_to_blog( $wpdb_blogid );
  //if(get_lastpostdate( 'blog' )){
    $lastpostdate = get_lastpostdate( 'blog' );
    $updated_array = array('last_updated' => $lastpostdate );
    $wpdb->update( $wpdb->blogs, $updated_array, array('blog_id' => $wpdb_blogid) );
    refresh_blog_details($wpdb_blogid);
  //}

  restore_current_blog();

}

/* Fast Spam Removal */
function fcc_kill_spam() {
  global $wpdb;
  $spam_comments_id_arr = $wpdb->get_col( "SELECT comment_id FROM {$wpdb->comments} WHERE comment_approved='spam' OR comment_approved='trash'" ) ;
  if ( !empty( $spam_comments_id_arr ) ) {
    $spam_comments_ids = implode( ', ', array_map('intval', $spam_comments_id_arr) );
    $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_id IN ( $spam_comments_ids )");
    $wpdb->query("DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ( $spam_comments_ids )");
    $wpdb->query( "OPTIMIZE TABLE $wpdb->comments" );
    $wpdb->query( "OPTIMIZE TABLE $wpdb->commentmeta" );
  }
  if ( is_admin() ) echo 'complete';
}

/**
* Available Shortcodes: Add Dropdown List Selector to TinyMCE Post Editor
* Documentation: http://wpsnipp.com/index.php/functions-php/update-automatically-create-media_buttons-for-shortcode-selection/#
* Added 11/23/15
*/
add_action('media_buttons','add_sc_select',11);
function add_sc_select(){

	global $shortcode_tags;

	/* enter names of shortcode to exclude below: */
	$exclude = array("wp_caption", "embed", "globalrecentposts");

	echo '&nbsp;<select id="sc_select"><option>Available Shortcodes</option>';

    foreach ($shortcode_tags as $key => $val){
            if(!in_array($key,$exclude)){
            $shortcodes_list .= '<option value="['.$key.'][/'.$key.']">'.$key.'</option>';
            }
        }
     echo $shortcodes_list;
     echo '</select>';
} //END add_sc_select

add_action('admin_head', 'button_js');
function button_js() {
        echo '<script type="text/javascript">
        jQuery(document).ready(function(){
           jQuery("#sc_select").change(function() {
                          send_to_editor(jQuery("#sc_select :selected").val());
                          return false;
                });
        });
        </script>';
} //End button_js


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
* Set New Blog Jetpack Default Options
* Added 10/15/15
*/
function fcc_new_site_options( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
  /* Jetpack Auto-Connect / Subsite Register */
    /*Set Variables*/
    //$super_admin = '1';
    //$current_user = get_current_user_id();
  //Set UserID to SuperAdmin for Jetpack Registration//
  /* if ( $current_user != $super_admin) { wp_set_current_user($super_admin); }
    $jetpack_network = Jetpack_Network::init();
    $jetpack_network->do_subsiteregister($blog_id);
  wp_set_current_user($current_user); //return to original user */


  //Switch to New Blog Site//
  switch_to_blog( $blog_id );

   //Jetpack Fallback Check//
    /* $jp_active = get_option('jetpack_activated');
    if ( $jp_active != 1 ) {
      if ( $current_user != $super_admin) { wp_set_current_user($super_admin); }
        $jetpack_network = Jetpack_Network::init();
        $jetpack_network->do_subsiteregister($blog_id);
      wp_set_current_user($current_user);
    } */

   //Set Posts-Per-Page//
   update_option( 'posts_per_page', '5' );

    //Set Theme//
    $current_theme = wp_get_theme();
     if ( $current_theme != 'AreaVoices') {
       switch_theme( 'areavoices', 'areavoices' );
     }

    //Delete Initial Blog Post//
    wp_delete_post( 1, 1 );

    //Delete Sample Page//
    wp_delete_post( 2, 1 );

   /*Dismiss Admin Nag*/
   //Jetpack_Options::update_option( 'dismissed_manage_banner', true );

   //Set Jetpack Sharing Services//
  /* $jp_sharing_services = array(
      'visible' =>
     array (
       0 => 'facebook',
       1 => 'twitter',
       2 => 'google-plus-1',
       3 => 'pinterest',
       4 => 'reddit',
     ),
      'hidden' =>
     array (
       0 => 'email',
       1 => 'linkedin',
       2 => 'tumblr',
       3 => 'pocket',
       4 => 'press-this',
       5 => 'print',
     ),
   );
   update_option( 'sharing-services', $jp_sharing_services ); */

   //Set Jetpack Sharing Options//
  /* $jp_sharing_options = array(
      'global' => (array( 'button_style' => 'icon', 'sharing_label' => false, 'open_links' => 'same',
        'show'=> array( 0 => 'index', 1 => 'post', ),
        'custom' => array(),
     )),
   );
   update_option( 'sharing-options', $jp_sharing_options ); */

   //Deactivate Widgets//
   fcc_clear_widgets();

   //Clear Options Cache to force update//
   wp_cache_delete ( 'alloptions', 'options' );

  //Return to Source//
  restore_current_blog();
}
//add_action( 'wpmu_new_blog', 'fcc_new_site_options', 10); /* 200 for post-Jetpack, 10 for pre-jetpack

/******************************************************************************

/*
* Admin Menu Setup
*/
add_action( 'admin_menu', 'fcc_admin_tools_menu' );
function fcc_admin_tools_menu()
{
  if ( is_super_admin() ) { //Add the page for Super Admins only
    add_submenu_page(
      'ms-admin.php',
      'FCC Admin',
      'FCC Admin',
      'manage_options',
      'fcc_admin',
      'fcc_admin_tools_page'
    );
    require plugin_dir_path( __FILE__ ) . 'fcc-admin-functions.php';
  }
}

/*
* Admin Page
*/
function fcc_admin_tools_page()
{
    global $blog_id, $wpdb, $wp_roles, $wp_rewrite, $current_user, $current_site;

?>
<div class="wrap">
<?php
    switch ( $_GET['action'] ) {
      //---------------------------------------------------//
        default:
?>
			<h2><?php _e( 'FCC Admin Tools' ) ?></h2>
			<h3><?php _e( 'New Blog Setup' ) ?></h3>
			<div class="wrap">
        <form method="post" action="ms-admin.php?page=fcc_admin&action=fccexec">
          <p class="submit">
            <input type="submit" class="button-primary" name="Submit" value="<?php _e( 'Run AV Blog Setup & Conversion' ) ?>" />
          </p>
        </form>
      </div>

			<?php
            break;
            //---------------------------------------------------//
        case "fccexec":
        echo '<h2>Status:</h2><br>';

        $blog_id = get_current_blog_id();

        //Clear Options Cache to force update//
        wp_cache_delete ( 'alloptions', 'options' );


        //Set Theme to 'AreaVoices'//
        fcc_set_av_theme();

        /* Set Timezone */
        update_option( 'timezone_string', 'America/Chicago' );
        echo '<li>Timezone set to "America/Chicago"</li>';

        //Set Homepage to diplay 'Posts'//
        fcc_set_homepage();

        //Set Posts-Per-Page to 5//
        fcc_set_posts_per_page();

        //Delete Initial Blog Post//
        fcc_delete_first_post();

        //Delete Sample Page//
        fcc_delete_first_page();

        //Insert Default Categories//
        fcc_insert_default_categories();
        echo '<li>Inserted default categories.</li>';

        //Set the Default Post Category//
        fcc_set_default_category();
        echo '<li>Default post category set to "News"</li>';

        //Deactivate Widgets//
        fcc_clear_widgets();
        echo '<li>All widgets set to "Inactive"</li>';

				//Mark Site as Verified//
				add_option( 'av-verified-site', '1' );
				echo '<li>Site marked as "Verified"</li>';

        /* Jetpack Activation */
        //fcc_jetpack_activate();

        /* Dismiss Jetpack Admin Nag */
        //fcc_jp_dismissed_manage_banner();

        /* Jetpack Activation */
        fcc_jetpack_setup();

        //Clear Options Cache to force update//
        wp_cache_delete ( 'alloptions', 'options' );

        echo '<p><em>Blog Setup/Conversion Complete!</em></p>';
        break;
    }
?>
</div>
<?php
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
