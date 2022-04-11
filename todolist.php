<?php 
/*
Elementor Todolist Wordpress Plugin

@package ElementorTodolist

Plugin Name: Todo List with Widget
Description: Nothing much. Just your old to do list compiler. Why not start your day by listing it out?
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
  add_menu_page('Todolist', 'Todolist', 'manage_options' ,__FILE__, 'license_credentials_page', 'dashicons-clipboard');
}

//admin page full features
function addAdminPageContent() {
  global $wpdb;
  $notif_tbl = $wpdb->prefix . 'laravel_data_notif_tbl';
  $msgs_count = $wpdb->get_var("SELECT count(*) FROM $notif_tbl WHERE status=1");
  add_menu_page('Todolist', 
  $msgs_count ? sprintf('Todolist <span class="awaiting-mod">%d</span>', $msgs_count) : 'Todolist', //notification bubble admin menu
                'manage_options' ,
                __FILE__, 
                'crudAdminPage', 
                'dashicons-clipboard');
  add_submenu_page(__FILE__, 'Button Tracker', 'Button Tracker', 'manage_options' , 'button_tracker_tab', 'button_tracker_list_tab');
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

//file inclusions for main features
require_once( __DIR__ . '/trunk/wordpressfunctions.php' );
require_once( __DIR__ . '/trunk/elementor-sub-functions.php' );
require_once( __DIR__ . '/trunk/btn-click-tracker.php' );
require_once( __DIR__ . '/trunk/admin-todolist-page.php');
require_once( __DIR__ . '/trunk/admin-btn-tracker-tab.php');
require_once( __DIR__ . '/trunk/activation.php' );
require_once( __DIR__ . '/trunk/license-validation.php');
require_once( __DIR__ . '/trunk/wp_retrieve_callback.php');

//send existing btn tracker count to laravel API
register_activation_hook( __FILE__, 'send_btn_count');
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

//adding plugin to admin menu
if(license_validation()){
  //bind full features
  add_action('admin_menu', 'addAdminPageContent');
  global $pagenow;
    if ( $pagenow == 'admin.php' ) :
      add_action('admin_notices','custom_admin_notice_popup');
    endif;
  
}else{
  add_action('admin_menu', 'require_activation');
}
