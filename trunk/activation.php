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

 
if ( ! defined( 'ABSPATH' ) ) {
  die; // Exit if accessed directly.
}

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
  // $sql = "TRUNCATE $table_name";
  // require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  // $wpdb->query($sql);
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
    `status`  varchar(50) DEFAULT NULL,
    `date_added`  datetime,
    PRIMARY KEY(id)
  );
  ";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
    }
}

//register new domain to laravel api and database table creation for license check
function registerDomain(){
  global $wpdb;
  $base_url = get_site_url();
  $charset_collate = $wpdb->get_charset_collate();
  date_default_timezone_set('Asia/Bangkok');
  $date = date('Y-m-d h:i:s');
  $table_name = $wpdb->prefix . 'license_check';
  $license_key_initial = 'N/A';
  $sql = "CREATE TABLE $table_name (
    `id` int(50) NOT NULL AUTO_INCREMENT,
    `base_url` varchar(100) DEFAULT NULL,
    `license_key`  varchar(50) DEFAULT NULL,
    `date_added`  datetime,
    PRIMARY KEY(id)
    );";
  $sql2 = "INSERT INTO $table_name(base_url, license_key, date_added) VALUES('$base_url','$license_key_initial','$date');";

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($sql2);
  }

  $status = 'Deactivated';
  $license_key = implode( '-', str_split( substr( strtoupper( md5( time() . rand( 1000, 9999 ) ) ), 0, 20 ), 4 ) );
  $send_domain_tb = array(
    'site_url' => $base_url,
    'date_added' => $date,
    'status' => $status,
    'license_key' => $license_key,
  );
  $data_push_to_api = json_encode($send_domain_tb);
  $send_domain = wp_remote_post('https://dashboard.sg-webdesign.net/registerurl', array(
    'method' => 'POST',
    'headers' => array(
      'Content-Type' => 'application/json'
    ),
      'sslverify' => false,
      'body' => $data_push_to_api,
    )); 
}

function laravel_notif_db_tbl(){
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'laravel_data_notif_tbl';
  $sql = "CREATE TABLE $table_name (
    `id` int(50) NOT NULL AUTO_INCREMENT,
    `notification` varchar(100) DEFAULT NULL,
    `status`  varchar(50) DEFAULT NULL,
    `date_added`  datetime,
    PRIMARY KEY(id)
  );
  ";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
    }
}