<?php

/**
 * Inserts the default post categories to the current blog.
 *
 * @since 2015.12.22
 *
 */
function fcc_insert_default_categories() {
  wp_insert_term( 'Arts & Entertainment', 'category', array( 'description'	=> '', 'slug' => 'arts-entertainment' ) );
  wp_insert_term( 'Automotive', 'category', array( 'description'	=> '', 'slug' => 'automotive' ) );
  wp_insert_term( 'Business', 'category', array( 'description'	=> '', 'slug' => 'business' ) );
  wp_insert_term( 'Careers', 'category', array( 'description'	=> '', 'slug' => 'careers' ) );
  wp_insert_term( 'Education', 'category', array( 'description'	=> '', 'slug' => 'education' ) );
  wp_insert_term( 'Family & Parenting', 'category', array( 'description'	=> '', 'slug' => 'family-parenting' ) );
  wp_insert_term( 'Food & Drink', 'category', array( 'description'	=> '', 'slug' => 'food-drink' ) );
  wp_insert_term( 'Health & Fitness', 'category', array( 'description'	=> '', 'slug' => 'health-fitness' ) );
  wp_insert_term( 'Hobbies & Interests', 'category', array( 'description'	=> '', 'slug' => 'hobbies-interests' ) );
  wp_insert_term( 'Home & Garden', 'category', array( 'description'	=> '', 'slug' => 'home-garden' ) );
  wp_insert_term( 'Law, Government, Politics', 'category', array( 'description'	=> '', 'slug' => 'law-government-politics' ) );
  wp_insert_term( 'News', 'category', array( 'description'	=> '', 'slug' => 'news' ) );
  wp_insert_term( 'Personal Finance', 'category', array( 'description'	=> '', 'slug' => 'personal-finance' ) );
  wp_insert_term( 'Pets', 'category', array( 'description'	=> '', 'slug' => 'pets' ) );
  wp_insert_term( 'Real Estate', 'category', array( 'description'	=> '', 'slug' => 'real-estate' ) );
  wp_insert_term( 'Religion & Spirituality', 'category', array( 'description'	=> '', 'slug' => 'religion-spirituality' ) );
  wp_insert_term( 'Science', 'category', array( 'description'	=> '', 'slug' => 'science' ) );
  wp_insert_term( 'Shopping', 'category', array( 'description'	=> '', 'slug' => 'shopping' ) );
  wp_insert_term( 'Society', 'category', array( 'description'	=> '', 'slug' => 'society' ) );
  wp_insert_term( 'Sports', 'category', array( 'description'	=> '', 'slug' => 'sports' ) );
  wp_insert_term( 'Style & Fashion', 'category', array( 'description'	=> '', 'slug' => 'style-fashion' ) );
  wp_insert_term( 'Technology & Computing', 'category', array( 'description'	=> '', 'slug' => 'technology-computing' ) );
  wp_insert_term( 'Travel', 'category', array( 'description'	=> '', 'slug' => 'travel' ) );
}

/**
 * Sets the default post category to 'News',
 *
 * @since 2015.12.22
 *
 */
function fcc_set_default_category() {
  $cat_name = 'News';
  $cat_id = get_cat_ID( $cat_name );
  update_option( 'default_category', $cat_id );
}

/**
 * Updates the 'Discussion' options of the current blog to FCC standards.
 *
 * @since 2015.12.22
 *
 */
function fcc_set_discussion_options() {
  update_option( 'default_pingback_flag', '0' );
  update_option( 'default_ping_status', '0' );
  update_option( 'default_comment_status', '1' );
  update_option( 'require_name_email', '1' );
  update_option( 'comment_registration', '0' );
  update_option( 'close_comments_for_old_posts', '1' );
  update_option( 'close_comments_days_old', '14' );
  update_option( 'page_comments', '1' );
  update_option( 'comments_per_page', '50' );
  update_option( 'default_comments_page', 'newest' );
  update_option( 'comment_order', 'desc' );
  update_option( 'comments_notify', '0' );
  update_option( 'moderation_notify', '1' );
  update_option( 'comment_moderation', '0' );
  update_option( 'comment_whitelist', '1' );
  update_option( 'comment_max_links', '1' );
  update_option( 'show_avatars', '1' );
  update_option( 'gravatar-hovercard-options', '1' );
  update_option( 'avatar_rating', 'PG' );
  update_option( 'avatar_default', 'mystery' );
  //update_option( 'moderation_keys', '...' );
  //update_option( 'blacklist_keys', '...' );
}


/*
* Clear Widgets:
* Sets all currently active widgets to inactive
*/
function fcc_clear_widgets() {
  $sidebars = wp_get_sidebars_widgets();
  $inactive = isset($sidebars['wp_inactive_widgets']) ? $sidebars['wp_inactive_widgets'] : array();

  unset($sidebars['wp_inactive_widgets']);

  foreach ( $sidebars as $sidebar => $widgets ) {
    $inactive = array_merge($inactive, $widgets);
    $sidebars[$sidebar] = array();
  }

  $sidebars['wp_inactive_widgets'] = $inactive;
  wp_set_sidebars_widgets( $sidebars );
  //echo '<li>All widgets set to "Inactive"</li>';
}

/*
* Set Theme to 'AreaVoices':
*/
function fcc_set_av_theme() {
  $current_theme = wp_get_theme();
   if ( $current_theme != 'AreaVoices') {
     switch_theme( 'areavoices', 'areavoices' );
     echo '<li>Theme Switched from "' . $current_theme . '"</li>';
   }
   $theme_status = wp_get_theme();
   if ( $theme_status == 'AreaVoices') { echo '<li>Theme Set to "' . $theme_status . '"</li>'; }
   else { echo '<strong>Error:<strong> Theme did not update correctly<br>'; }
}

/*
* Set front page to display posts:
*/
function fcc_set_homepage() {
  $frontpage_displays = get_option('show_on_front');
  if ( $frontpage_displays != 'posts' ) {
  	update_option( 'show_on_front', 'posts' );
  	//echo '<li>Front page set to display "Your latest posts"</li>';
  }
  echo '<li>Front page set to display "Your latest posts"</li>';
  //echo get_option('show_on_front');
}

/*
* Set posts-per-page to '5'
*/
function fcc_set_posts_per_page() {
  update_option( 'posts_per_page', '5' );
  echo '<li>Posts-Per-Page Set to "5"</li>';
  //$postsperpage_status = get_option('posts_per_page');
  //if ($postsperpage_status == 5 ) { echo '<li>Posts-Per-Page Set to "5"</li>'; }
  //else { echo '<strong>Error:<strong> Posts-Per-Page did not update correctly<br>'; }
}

/*
* Delete 'Hello World' initial blog post
*/
function fcc_delete_first_post() {
  $first_post = get_post('1');
  if ( $first_post ) {
  	wp_delete_post( 1, 1 );
  	echo '<li>Initial "Hello World!" Post Deleted</li>';
  }
}

/*
* Delete 'Sample Page' initial page
*/
function fcc_delete_first_page() {
  $first_page = get_post('2');
  if ( $first_page ) {
    wp_delete_post( 2, 1 );
    echo '<li>Initial "Sample Page" Deleted</li>';
  }
}

/*****************************
* Jetpact Activation & Setup *
*****************************/
/*
* Jetpack Activation:
* Connects Jetpack if not currently active
*/
function fcc_jetpack_activate() {
  $jp_active = get_option('jetpack_activated');
  if ( $jp_active != 1 ) {
   if ( $current_user != $super_admin) { wp_set_current_user($super_admin); }
     $jetpack_network = Jetpack_Network::init();
     $jetpack_network->do_subsiteregister($blog_id);
   wp_set_current_user($current_user);
   $jp_status = get_option('jetpack_activated');
   if ($jp_status == 1 ) { echo '<li>Jetpack Activated!</li>'; }
  }
}

/*
* Dismiss Jetpack Admin Nag
*/
function fcc_jp_dismissed_manage_banner() {
  if ( !Jetpack_Options::get_option( 'dismissed_manage_banner') ) {
    Jetpack_Options::update_option( 'dismissed_manage_banner', true );
    echo '<li>Jetpack "Manage" Dashboard Banner Dismissed</li>';
  }
}


function fcc_jetpack_setup() {
  /*
  * Set Jetpack Sharing Services:
  */
   $jp_sharing_services = array(
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
   update_option( 'sharing-services', $jp_sharing_services );

   /*
   * Set Jetpack Sharing Options:
   */
   $jp_sharing_options = array(
      'global' => (array( 'button_style' => 'icon', 'sharing_label' => false, 'open_links' => 'same',
        'show'=> array( 0 => 'index', 1 => 'post', 2 => 'podcasts' ),
        'custom' => array(),
     )),
   );
   update_option( 'sharing-options', $jp_sharing_options );
   echo '<li>Jetpack Social Sharing Options Set</li>';

}

?>
