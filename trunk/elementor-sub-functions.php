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


 // ELEMENTOR SUBMISSIONS --AREA-- #############
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
    $datasend2 = array();
    //get all subs
    $results =  $wpdb->get_results("SELECT id, 
                                           post_id, 
                                           referer, 
                                           referer_title, 
                                           form_name, 
                                           created_at, 
                                           user_ip, 
                                           user_agent FROM $subs_db
                                    ORDER BY ID ASC");
    $results2 = $wpdb->get_results("SELECT * FROM $subval_db ORDER BY ID ASC");
    //set data retrieval fields
    if(!empty($results)){
      foreach($results as $result){
          $post_data = [
            'sub_id'        => $result->id,
            'post_id'       => $result->post_id,
            'base_url'      => $base_url,
            'ref_link'      => $result->referer,
            'ref_title'     => $result->referer_title,
            'form_name'     => $result->form_name,
            'date_received' => $result->created_at,
            'user_ip'       => $result->user_ip,
            'user_agent'    => $result->user_agent
          ];
          array_push($datasend, $post_data);
      }
      if(!empty($results2)){
        foreach($results2 as $res){
          $sub_values = [
            'submission_id' => $res->submission_id,
            'base_url'      => $base_url,
            'vkey'          => $res->key,
            'value'         => $res->value
          ];
          array_push($datasend2, $sub_values);
        }
      }
      // post to laravel api then store to its database
      $data_push_to_api = json_encode($datasend); //sub record
      $data_push_to_api2 = json_encode($datasend2); //sub form values
      $url = 'https://dashboard.sg-webdesign.net/retrievesubs';
      $url2 = 'https://dashboard.sg-webdesign.net/savesubvalues';
      //send wp remote post for sub main record
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
      //send wp remote post for sub form values
      $arguments2 = array(
            'method' => 'POST',
            'headers' => array(
            'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
            'body' => $data_push_to_api2,
            );
      //execute api request
      $response2 = wp_remote_post($url2, $arguments2);     
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
    $results =  $wpdb->get_row("SELECT id, 
                                       post_id, 
                                       referer, 
                                       referer_title, 
                                       form_name, 
                                       created_at, 
                                       user_ip, 
                                       user_agent FROM $subs_db
                                ORDER BY ID DESC LIMIT 1");
    $results2 = $wpdb->get_results("SELECT * FROM $subval_db WHERE submission_id='$results->id'"); 
    //set fetched db elementor submission data
      $post_data = array(
        'sub_id'        => $results->id,
        'post_id'       => $results->post_id,
        'base_url'      => $base_url,
        'ref_link'      => $results->referer,
        'ref_title'     => $results->referer_title,
        'form_name'     => $results->form_name,
        'date_received' => $results->created_at,
        'user_ip'       => $results->user_ip,
        'user_agent'    => $results->user_agent
      );

    //set fetched db elementor submission values
    $datasend = array();
      if(!empty($results2)){
        foreach($results2 as $res){
          $sub_values = [
            'submission_id' => $res->submission_id,
            'base_url'      => $base_url,
            'vkey'          => $res->key,
            'value'         => $res->value
          ];
          array_push($datasend, $sub_values);
        }
      }
    //set api request config
    $data_push_to_api = json_encode($post_data);
    $data_push_to_api2 = json_encode($datasend);
    $url = 'https://dashboard.sg-webdesign.net/savesub';
    $url2 = 'https://dashboard.sg-webdesign.net/savesubvalues';
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
    //execute api request for sub values
    $arguments2 = array(
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json',
      ),
      'sslverify' => false,
      'body' => $data_push_to_api2,
    );
    //execute api request
    $response2 = wp_remote_post($url2, $arguments2);
	//var_dump($response);
  }
