<?php
/*
 * Plugin Name: Social Monitor
 * Version: 1.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: This is your starter template for your next WordPress plugin.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: social-monitor
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-social-monitor.php' );
require_once( 'includes/class-social-monitor-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-social-monitor-admin-api.php' );
require_once( 'includes/lib/class-social-monitor-post-type.php' );
require_once( 'includes/lib/class-social-monitor-taxonomy.php' );

/**
 * Returns the main instance of Social_Monitor to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Social_Monitor
 */
function Social_Monitor () {
	$instance = Social_Monitor::instance( __FILE__, '1.0.0' );
  
  $instance->register_taxonomy('sm_social_tag', 'Social Tags', 'Social Tag', 'sm_social_post' );
  $term = get_term("Social Post", 'sm_social_tag');
  if (!$term) {
    wp_insert_term('Social Post', 'sm_social_tag');
  }
  
  $args =  array(
    'labels' => array(
      'name' => 'Social Posts',
      'singular_name' => "Social Post"
    ),
    'taxonomies' => array( 'category', 'sm_social_tag' )
  );
  
	$instance->register_post_type('sm_social_post', "Social Importers", "Social Importer", 'Imports posts from various social networks based on various rules set in the Social Monitor plugin settings.', $args );

  $args = array(
    'public' => false,
    'taxonomies' => array('sm_social_tag'),
		'exclude_from_search' => true,
		'show_ui' => false,
		'show_in_menu' => false,
		'show_in_nav_menus' => false,
  );
  
  $instance->register_post_type('sm_social_importer', "Social Importers", "Social Importer", "Controls how posts are imported from social feeds, based on rules set in the Social Monitor settings.", $args);

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Social_Monitor_Settings::instance( $instance );
	}

	return $instance;
}


// !!!!!!!!!!!!!!!!
//
// add_filter( 'manage_ch_social_post_posts_columns', 'add_ch_social_post_header_columns' ) ;
// 
// function add_ch_social_post_header_columns( $columns ) {
// 
// 	$columns = array(
// 		'cb' => '<input type="checkbox" />',
// 		'title' => __( 'Social Post' ),
// 		'type' => __( 'Service' ),
// 		'visible' => __( 'Visible' ),
// 		'date' => __( 'Date' )
// 	);
// 
// 	return $columns;
// 
// }
// 
// add_action( 'manage_ch_social_post_posts_custom_column', 'add_ch_social_post_columns', 10, 2 );
// 
// function add_ch_social_post_columns( $column, $post_id ) {
// 
// 	global $post;
// 
// 	switch( $column ) {
// 
// 		case 'type':
// 		
// 		echo ucfirst( get_post_meta( $post_id, 'service', true ) );
// 
// 		break;
// 		
// 		case 'visible':
// 		
// 		echo ( get_post_meta( $post_id, 'published', true ) ) ? 'Visible' : '';
// 
// 		break;
// 
// 	}
// 
// }

// 
// add_filter( 'cron_schedules', 'add_custom_cron_intervals', 10, 1 );
// 
// function add_custom_cron_intervals( $schedules ) {
// 
// 	$schedules['ten_minutes'] = array(
// 		'interval'	=> 600,
// 		'display'	=> 'Once Every 10 Minutes'
// 	);
// 
// 	return (array)$schedules; 
// 	
// }
// 
// register_deactivation_hook( __FILE__, 'lb_social_media_plugin_deactivate' );
// 
// function lb_social_media_plugin_deactivate() {
// 
// 	wp_clear_scheduled_hook( 'update_social_posts' );
// 	
// }
// 
// 
// register_activation_hook( __FILE__, 'lb_social_media_plugin_register' );
// 
// function lb_social_media_plugin_register() {
// 	
// 	wp_schedule_event( time(), 'ten_minutes', 'update_social_posts' );
// 	
// }

add_action( 'update_social_posts', 'sm_update_social_monitor' );

if( $_GET['action'] == 'update_social_monitor' ){
	
	add_action( 'init', 'sm_update_social_monitor' );
	
}

function sm_update_social_monitor(){
	sm_update_instagram_monitor();
}

function sm_update_instagram_monitor() {
	foreach ($social_monitor->get_instagram_posts() as $social_post) {
				
		$instagram_post = new Instagram_Post($social_post->id, $social_post->caption->text, $social_post->user->username, $social_post->images->standard_resolution->url, $social_post->videos->standard_resolution->url, $social_post->link, $social_post->created_time);
		
		$results = get_posts( array( 'post_type' => 'sm_social_post', 'posts_per_page' => 1, 'meta_key' => 'service_id', 'meta_value' => $social_post->id ) );
		
		if( count( $results ) > 0 ){
			break;
		}
		
		$new_post_id = $instagram_post->save();
		
	}  
}


//!!!!!!!!!!!!!!!!!!!!!

Social_Monitor();
