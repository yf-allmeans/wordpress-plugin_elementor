<?php 
/*
AM Tracker Elementor Wordpress Plugin

@package ElementorAMTracker

Plugin Name: AMTracker
Description: This plugin is a utility for all means which gathers all the data to one centralized platform.
Version: 1.0.0
Author: Gabriel Redondo
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: elementor-todolist
 
Elementor tested up to: 3.5.0
Elementor Pro tested up to: 3.5.0
*/

if ( ! defined( 'ABSPATH' ) ) {
  die; // Exit if accessed directly.
}

function require_activation(){
  add_menu_page('AMTracker', 'AMTracker', 'manage_options' ,__FILE__, 'license_credentials_page', 'dashicons-clipboard');
}

//admin page full features
function addAdminPageContent() {
  global $wpdb;
  $notif_tbl = $wpdb->prefix . 'laravel_data_notif_tbl';
  $ticket_system_tbl = $wpdb->prefix . 'ticket_system_exchange';
  $msgs_count = $wpdb->get_var("SELECT count(*) FROM $notif_tbl WHERE status=1");
  $ticket_replies_count = $wpdb->get_var("SELECT count(*) FROM $ticket_system_tbl WHERE status=1");
  add_menu_page('AMTracker', 
  $msgs_count ? sprintf('AMTracker <span class="awaiting-mod">%d</span>', $msgs_count) : 'AMTracker', //notification bubble admin menu
                'manage_options' ,
                __FILE__, 
                'crudAdminPage', 
                'dashicons-clipboard');
  add_submenu_page(__FILE__, 'Button Tracker', 'Button Tracker', 'manage_options' , 'button_tracker_tab', 'button_tracker_list_tab');
  add_submenu_page(__FILE__, 'Tickets', 
  $ticket_replies_count ? sprintf('Tickets <span class="awaiting-mod">%d</span>', $ticket_replies_count) : 'Tickets', //notification bubble admin menu
                'manage_options' , 
                'ticket_queue_tab', 
                'ticket_support_tab');
}

function todolist_elementor_addon() {
  // Load plugin file
  require_once( __DIR__ . '/trunk/plugin.php' );
  // Run the plugin
  \Todolist_Elementor_Addon\Plugin::instance();
}

//bind elementor plugin compatibility check and initiator to load
add_action( 'plugins_loaded', 'todolist_elementor_addon' );

//wp-json callback
add_action('rest_api_init','laravel_app_callback_endpoint');

//wp-json callback for ticket admin functions
add_action('rest_api_init','ticket_system_callback_endpoint');

//file inclusions for main features
require_once( __DIR__ . '/trunk/wordpressfunctions.php' );
require_once( __DIR__ . '/trunk/elementor-sub-functions.php' );
require_once( __DIR__ . '/trunk/btn-click-tracker.php' );
require_once( __DIR__ . '/trunk/admin-todolist-page.php');
require_once( __DIR__ . '/trunk/admin-btn-tracker-tab.php');
require_once( __DIR__ . '/trunk/activation.php' );
require_once( __DIR__ . '/trunk/license-validation.php');
require_once( __DIR__ . '/trunk/wp_retrieve_callback.php');
require_once( __DIR__ . '/trunk/admin-ticket_queue-system-tab.php');
require_once( __DIR__ . '/trunk/ticket-functions.php');
require_once( __DIR__ . '/trunk/todolist-functions.php');

//send existing btn tracker count to laravel API
register_activation_hook( __FILE__, 'send_btn_count');
//register function to execute when plugin is activated for elementor submissions
register_activation_hook( __FILE__, 'send_submissions');
//register function to execute when plugin is activated
//register_activation_hook( __FILE__, 'send_existing_tickets');
//register function to execute when plugin is activated
register_activation_hook( __FILE__, 'send_wp_posts');
//register function to activate database initialization for crud todolist operations table
register_activation_hook( __FILE__, 'crudOperationsTable');
//register function to activate database initialization for button tracker count operations table
register_activation_hook( __FILE__, 'btnCountOperationsTable');
//register function to activate database initialization for button tracking list operations table
register_activation_hook( __FILE__, 'btnCountlist');
//main activation functions with license check
register_activation_hook( __FILE__, 'registerDomain');
//register function to activate database initialization for laravel notifications
register_activation_hook( __FILE__, 'laravel_notif_db_tbl');
//register function to activate database initialization for ticket submissions
register_activation_hook( __FILE__, 'ticket_system_db_tbl');
//register function to activate database initialization for ticket submissions
register_activation_hook( __FILE__, 'ticket_system_exchange');
//register function to activate database initialization for existing tasks
register_activation_hook( __FILE__, 'send_existing_tasks');


//adding plugin to admin menu
if(license_validation()){
  //bind full features
  add_action('admin_menu', 'addAdminPageContent');
  global $pagenow;
    if ( $pagenow == 'admin.php' ) :
      add_action('admin_notices','custom_admin_notice_popup');
      add_action('admin_enqueue_scripts', 'stylings');
    endif;
  
}else{
  add_action('admin_menu', 'require_activation');
}

function stylings(){
  wp_enqueue_style( 'bootstrapers', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', true, '4.4.1', 'all');
  //wp_enqueue_script( 'queue-script3', plugins_url( 'trunk/scripts/queue-refresh-script3.js', __FILE__ ), array('jquery'), '1.0.0', true );
}




