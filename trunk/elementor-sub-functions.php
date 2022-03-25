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


 // ELEMENTOR SUBMISSIONS --AREA-- #############

//register function to execute when plugin is activated
register_activation_hook( __FILE__, 'send_submissions');
//bind action to form submission via elementor
add_action( 'elementor_pro/forms/new_record', 'new_subs', 10, 2);

//get subs data and send to laravel
function send_submissions() {
    global $wpdb;
    $base_url = get_site_url();
    $subs_db = $wpdb->prefix . 'e_submissions';
    $subval_db = $wpdb->prefix . 'e_submissions_values';
    $post_data = array(
      'base_url' =>      $base_url,
    );
    
    //clear existing subs first
    $data_push_to_api = json_encode($post_data);
    $truncate = wp_remote_post('https://dashboard.sg-webdesign.net/truncatesub', array(
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json',
      ),
      'body' => $data_push_to_api,
    )); 
    $datasend = array();
    //get all subs
    $results =  $wpdb->get_results("SELECT s.id, s.post_id, s.referer, s.created_at, s.user_id, v.value as user_email FROM $subs_db s 
                                    INNER JOIN $subval_db v ON s.id = v.submission_id 
                                    WHERE v.key = 'email'
                                    ORDER BY ID ASC");
    //api call
    if($wpdb->num_rows>0){
      foreach($results as $result){
          $post_data = [
            'user_id' => $result->user_id,
            'user_email' => $result->user_email,
            'base_url' => $base_url,
            'sub_id' => $result->id,
            'post_id' => $result->post_id,
            'ref_link' => $result->referer,
            'date_received' => $result->created_at
          ];
          array_push($datasend, $post_data);
      }
      // post to laravel api then store to its database
      $data_push_to_api = json_encode($datasend);
      $url = 'https://dashboard.sg-webdesign.net/retrievesubs';
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
	//var_dump($truncate);
  }

  //sending the new submission records to laravel api
  function new_subs() {
  	global $wpdb;
    $subs_db = $wpdb->prefix . 'e_submissions';
    $subval_db = $wpdb->prefix . 'e_submissions_values';
    $base_url = get_site_url();
    // $current_user = get_current_user_id();
    // $userinfo = get_userdata($current_user);
    // $user_email = $userinfo->user_email;
    $results =  $wpdb->get_results("SELECT s.id, s.post_id, s.referer, s.created_at, s.user_id, v.value as user_email FROM $subs_db s 
                                    INNER JOIN $subval_db v ON s.id = v.submission_id 
                                    WHERE v.key = 'email'
                                    ORDER BY ID DESC LIMIT 1");
    //set fetched db elementor submission data
      foreach($results as $res){
      $post_data = array(
        'user_id' =>       $res->user_id,
        'user_email' =>    $res->user_email,
        'base_url' =>      $base_url,
        'sub_id' =>        $res->id,
        'post_id' =>       $res->post_id,
        'ref_link' =>      $res->referer,
        'date_received' => $res->created_at
      );
    }
    //set api request config
    $data_push_to_api = json_encode($post_data);
    $url = 'https://dashboard.sg-webdesign.net/savesub';
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
	//var_dump($response);
  }
