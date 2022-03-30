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
 
*/

if ( ! defined( 'ABSPATH' ) ) {
  die; // Exit if accessed directly.
}

function todolist_elementor_addon() {
 
  // Load plugin file
  require_once( __DIR__ . '/trunk/plugin.php' );
  // Run the plugin
  \Todolist_Elementor_Addon\Plugin::instance();

}
add_action( 'plugins_loaded', 'todolist_elementor_addon' );

//adding plugin to admin menu
add_action('admin_menu', 'addAdminPageContent');

function addAdminPageContent() {
  add_menu_page('Todolist', 'Todolist', 'manage_options' ,__FILE__, 'crudAdminPage', 'dashicons-clipboard');
  add_submenu_page(__FILE__, 'Button Tracker', 'Button Tracker', 'manage_options' , 'button_tracker_tab', 'button_tracker_list_tab');
}

//file inclusions for main features
require_once( __DIR__ . '/trunk/activation.php' );
require_once( __DIR__ . '/trunk/wordpressfunctions.php' );
require_once( __DIR__ . '/trunk/elementor-sub-functions.php' );
require_once( __DIR__ . '/trunk/btn-click-tracker.php' );
require_once( __DIR__ . '/trunk/admin-todolist-page.php');
require_once( __DIR__ . '/trunk/admin-btn-tracker-tab.php');

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


