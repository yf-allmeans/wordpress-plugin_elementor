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

//get ticket data and send to laravel
function send_existing_tickets(){
    global $wpdb;
    $base_url = get_site_url();
    $ticket_tbl = $wpdb->prefix . 'ticket_system_tbl';
    $ticket_exchange_tbl = $wpdb->prefix . 'ticket_system_exchange';
    $ticket_url = array(
        'base_url' =>      $base_url,
    );
    //set api request config for truncate
    $data_push_to_api = json_encode($ticket_url);
    //clear data from laravel
    $truncate = wp_remote_post('https://dashboard.sg-webdesign.net/truncatetickets', array(
        'method' => 'POST',
        'headers' => array(
          'Content-Type' => 'application/json'
        ),
          'sslverify' => false,
          'body' => $data_push_to_api,
        )); 
    //get all existing ticket data in wordpress
    $datasend = array();
    $existingtickets = $wpdb->get_results("SELECT * FROM $ticket_tbl ORDER BY ID ASC");
    if(!empty($existingtickets)){
        foreach($existingtickets as $ticket){
            //configure data to send
            $ticket_data = [
            'ticket_id' =>      $ticket->id,
            'base_url' =>       $base_url,
            'queue' =>          $ticket->queue,
            'subject' =>        $ticket->subject,
            'task_type' =>      $ticket->task_type,
            'content' =>        $ticket->content,
            'status' =>         $ticket->status,
            'last_modified' =>  $ticket->last_modified,
            'date_added' =>     $ticket->date_added,
            ];
            array_push($datasend, $ticket_data);
          }
          // post to laravel api then store to its database
          $data_push_to_api = json_encode($datasend);
          $url = 'https://dashboard.sg-webdesign.net/retrievetickets';
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
    var_dump($response);
    //get all existing ticket exchange data in wordpress
    $datasend2 = array();
    $existingexchange = $wpdb->get_results("SELECT * FROM $ticket_exchange_tbl ORDER BY ID ASC");
    if(!empty($existingexchange)){
        foreach($existingexchange as $exchange){
            //configure data to send
            $exchange_data = [
            'ticket_id' =>      $exchange->ticketid,
            'base_url' =>       $exchange->base_url,
            'content' =>        $exchange->content,
            'sender' =>         $exchange->sender,
            'status' =>         $exchange->status,
            'date_sent' =>      $exchange->date_sent,
            ];
            array_push($datasend2, $exchange_data);
          }
          // post to laravel api then store to its database
          $data_push_to_api2 = json_encode($datasend2);
          $url2 = 'https://dashboard.sg-webdesign.net/retrievetickexchange';
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
  }

  
  function ticket_system_callback_endpoint(){

    //establish the callback namespace and endpoint for WP/JSON for admin update
    register_rest_route(
        'ticketadminupdate/v1/', //Namespace
        'receive-callback', //Endpoint
        array(
            'methods'  => 'POST',
            'callback' => 'ticketadminupdate_receive_callback'
        )
    );

    //establish the callback namespace and endpoint for WP/JSON for admin response/ticket system exchange
    register_rest_route(
      'ticketadminreply/v1/', //Namespace
      'reply-callback', //Endpoint
      array(
          'methods'  => 'POST',
          'callback' => 'ticketadminreply_receive_callback'
      )
    );

    //establish the callback namespace and endpoint for WP/JSON for admin delete ticket system exchange/comment
    register_rest_route(
      'ticketexchangedel/v1/', //Namespace
      'del-callback', //Endpoint
      array(
          'methods'  => 'POST',
          'callback' => 'ticketexchangedel_del_callback'
      )
    );

  }

  //get admin update callback from laravel api
  function ticketadminupdate_receive_callback($request_data){
    global $wpdb;
    $ticket_system_tbl = $wpdb->prefix . 'ticket_system_tbl';
    $ticketinfo = $request_data->get_params();
    //get parameterrs
    $id =            $ticketinfo['ticket_id'];
    $subject =       esc_attr($ticketinfo['subject']);
    $content =       esc_attr($ticketinfo['content']);
    $task_type =     $ticketinfo['task_type'];
    $status =        $ticketinfo['status'];
    $last_modified = $ticketinfo['last_modified'];
        //run query for update
        $wpdb->query("UPDATE $ticket_system_tbl SET 
        subject=      '$subject', 
        content=      '$content',  
        task_type=    '$task_type', 
        status=       '$status', 
        last_modified='$last_modified' 
        WHERE id=     '$id'");
                  
    //$data = array();
    // $data['status'] = 'OK';
    // $data['received_data'] = array(
    //     'message' => $message,
    // );
    // $data['message'] = 'You have reached the server.';
    // return $data;
  }

  //admin reply route from laravel
  function ticketadminreply_receive_callback($request_data){
    global $wpdb;
    $ticket_tbl = $wpdb->prefix . 'ticket_system_tbl';
    $ticket_system_tbl = $wpdb->prefix . 'ticket_system_exchange';
    $ticketinfo = $request_data->get_params();
    $id =        $ticketinfo['ticket_id'];
    $content =   esc_attr($ticketinfo['content']);
    $sender =    $ticketinfo['sender'];
    $date_sent = $ticketinfo['date_sent'];
    $status = 1;

    //run query for update ticket last modified
    $wpdb->query("UPDATE $ticket_tbl SET last_modified='$date_sent' WHERE id='$id'");
        //run query for update
    $query = $wpdb->query("INSERT INTO $ticket_system_tbl(ticketid, content, sender, status, date_sent)
                      VALUES('$id', '$content', '$sender', '$status', '$date_sent');");
      
    // $data = array();
    // $data['status'] = 'OK';
    // $data['received_data'] = array(
    //     'message' => $wpdb->last_error,
    // );
    // $data['message'] = 'You have reached the server.';
    // return $data;
  }

  //admin delete route from laravel
  function ticketexchangedel_del_callback($request_data){
    global $wpdb;
    $ticket_tbl = $wpdb->prefix . 'ticket_system_tbl';
    $ticket_system_tbl = $wpdb->prefix . 'ticket_system_exchange';
    date_default_timezone_set('Asia/Singapore');
    $date = date('Y-m-d H:i:s');
    $ticketinfo = $request_data->get_params();
    $id =        $ticketinfo['ticket_id'];
    $content =   esc_attr($ticketinfo['content']);
    $sender =    $ticketinfo['sender'];
    $date_sent = $ticketinfo['date_sent'];
    //run query for update ticket last modified
    $wpdb->query("DELETE FROM $ticket_system_tbl 
                  WHERE ticketid='$id' 
                  AND content='$content'
                  AND sender='$sender'
                  AND date_sent='$date_sent'");
    
    //run query for update ticket last modified
    $wpdb->query("UPDATE $ticket_tbl SET last_modified='$date' WHERE id='$id'");
  }

?>