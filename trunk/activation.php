<?php
/**
 * Elementor_Todolist class.
 *
 * @category   Class
 * @package    ElementorTodolist
 * @subpackage WordPress
 * @author     Gabriel Redondo
 * @copyright  2022 Gabriel Redondo
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 * @since      1.0.0
 * php version 7.3.9
 */

//database table creation for todolist
function crudOperationsTable() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'todo';
  $sql = "CREATE TABLE `$table_name` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `todo` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `date` datetime,
  PRIMARY KEY(id)
  );
  ";
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}

//database table creation for button counter list
function btnCountOperationsTable(){
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'get_btn_count';
  $sql = "CREATE TABLE `$table_name` (
    `id` int(50) NOT NULL AUTO_INCREMENT,
    `btn_id` varchar(100) DEFAULT NULL,
    `post_id` varchar(100) DEFAULT NULL,
    `base_url`  varchar(100) DEFAULT NULL,
    `post_link`  varchar(100) DEFAULT NULL,
    `count`  bigint(100) NOT NULL,
    `user_ip`  varchar(100) DEFAULT NULL,
    PRIMARY KEY(id)
  );
  ";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
    }
}

//database table creation for button track list
function btnCountlist(){
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'btn_track_list';
  $sql = "CREATE TABLE $table_name (
    `id` int(50) NOT NULL AUTO_INCREMENT,
    `btn_id` varchar(100) DEFAULT NULL,
    `base_url`  varchar(100) DEFAULT NULL,
    `date_added`  datetime,
    PRIMARY KEY(id)
  );
  ";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
    }
}