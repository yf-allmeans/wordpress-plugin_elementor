<?php
/**
 * Elementor_AMtracker class.
 *
 * @category   Class
 * @package    ElementorAMTracker
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

function send_existing_tasks(){
    global $wpdb;
    $base_url = get_site_url();
    $todo_tbl = $wpdb->prefix . 'todo';
    $post_data = array(
        'base_url' =>      $base_url,
      );
    //clear existing tasks first
    $data_push_to_api = json_encode($post_data);
    $truncate = wp_remote_post('https://dashboard.sg-webdesign.net/truncatetasks', array(
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json',
      ),
      'body' => $data_push_to_api,
    )); 
    //get all existing tasks from wordpress
    $datasend = array();
    $existing_tasks = $wpdb->get_results("SELECT * FROM $todo_tbl ORDER BY ID ASC");
    //api call
    if(!empty($existing_tasks)){
        foreach($existing_tasks as $task){
            $post_data = [
              'wp_id'         => $task->id,
              'base_url'      => $base_url,
              'todo'          => $task->todo,
              'status'        => $task->status,
              'date_added'    => $task->date,
            ];
            array_push($datasend, $post_data);
        }
    $data_push_to_api = json_encode($datasend);
    $url = 'https://dashboard.sg-webdesign.net/retrievetasks';
      $arguments = array(
          'method' => 'POST',
          'headers' => array(
          'Content-Type' => 'application/json',
          ),
          'sslverify' => false,
          'body' => $data_push_to_api,
          );
          //execute api request
          $response = wp_remote_post($url, $arguments);   
    }
}
?>