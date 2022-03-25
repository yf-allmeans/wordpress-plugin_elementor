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

 // WORDPRESS POSTS --AREA-- #############

 //register function to execute when plugin is activated
register_activation_hook( __FILE__, 'send_wp_posts');
//bind action to execute everytime user creates new post also send to laravel
add_action( 'publish_post', 'new_wp_post', 10, 1 );
//bind action to execute everytime user deletes post also delete in laravel
add_action( 'delete_post', 'delete_wp_post', 10, 1 );
//bind action to execute everytime user update post also delete in laravel
add_action( 'post_updated', 'update_wp_post', 10, 1 );

//get posts data and send to laravel
function send_wp_posts() {
    global $wpdb;
    // $current_user = get_current_user_id();
    // $userinfo = get_userdata($current_user);
    // $user_email = $userinfo->user_email;
    $post_tb = $wpdb->posts;
    $user_tb = $wpdb->prefix . 'users';
    $base_url = get_site_url();
    $post_data = array(
      'base_url' =>      $base_url,
    );
    //set api request config
    $data_push_to_api = json_encode($post_data);
    $truncate = wp_remote_post('https://dashboard.sg-webdesign.net/truncate', array(
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json'
      ),
        'sslverify' => false,
        'body' => $data_push_to_api,
      )); 
    $datasend = array();
    //get all posts
    $results =  $wpdb->get_results("SELECT p.post_author, p.ID, p.guid, p.post_modified, u.user_email 
                                    FROM $post_tb p INNER JOIN $user_tb u ON p.post_author = u.ID WHERE p.post_type='post' ORDER BY ID ASC");
    //api call
    if($wpdb->num_rows>0){
      foreach($results as $result){
          $post_data = [
            'user_id' =>       $result->post_author,
            'user_email'=>     $result->user_email,
            'base_url'=>       $base_url,
            'post_id' =>       $result->ID,
            'post_link' =>     $result->guid,
            'post_modified' => $result->post_modified
          ];
          array_push($datasend, $post_data);
      }
      // post to laravel api then store to its database
      $data_push_to_api = json_encode($datasend);
      $url = 'https://dashboard.sg-webdesign.net/retrieveposts';
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
    date_default_timezone_set('Asia/Bangkok');
    $date = date('Y-m-d h:i:s');
    $send_domain_tb = array(
      'site_url' => $base_url,
      'date_added' => $date,
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
    //var_dump($send_domain);
  
    // if(is_wp_error($response)){
    //   $error_message = $response->get_error_message();
    //   echo 'Something went wrong!: $error_message';
    //   echo '<script>console.log($response)</script>';
    // }
    // else{
    //   echo 'Response:<pre>';
    //   print_r($response);
    //   echo '</pre>';
    // }
}


//main function new post
function new_wp_post($post_id){
     global $wpdb;
     $post_tb = $wpdb->posts;
     $user_tb = $wpdb->prefix . 'users';
     $base_url = get_site_url();
    // $post = get_post($post_id);
    // $current_user = get_current_user_id();
    // $userinfo = get_userdata($current_user);
    // $user_email = $userinfo->user_email;
    $posts =  $wpdb->get_results("SELECT p.post_author, p.ID, p.guid, p.post_modified, u.user_email 
    FROM $post_tb p INNER JOIN $user_tb u ON p.post_author = u.ID WHERE p.post_type='post' AND p.ID = $post_id ORDER BY ID ASC");
    //set post details
    if($wpdb->num_rows>0){
      foreach($posts as $post){
          $post_data = [
            'user_id' =>       $post->post_author,
            'user_email'=>     $post->user_email,
            'base_url'=>       $base_url,
            'post_id' =>       $post->ID,
            'post_link' =>     $post->guid,
            'post_modified' => $post->post_modified
          ];
      }
    }
    //set api request config
    $data_push_to_api = json_encode($post_data);
    $url = 'https://dashboard.sg-webdesign.net/save';
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

//delete post function
function delete_wp_post($post_id){
  $base_url = get_site_url();
  $post_data = array(
    'post_id' => $post_id,
    'base_url' => $base_url,
  );
  $data_push_to_api = json_encode($post_data);
  //set api request config for post delete
    $url = 'https://dashboard.sg-webdesign.net/wpdelete';
    $arguments = array(
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json',
      ),
      'sslverify' => false,
      'body' => $data_push_to_api,
    );
    //execute delete request
    $response = wp_remote_post($url, $arguments);  
    //var_dump($response);
}

//when user updates post, updated details are also sent to laravel api
function update_wp_post($post_id){
  global $wpdb;
  $post_tb = $wpdb->posts;
  $user_tb = $wpdb->prefix . 'users';
  $base_url = get_site_url();
  $posts =  $wpdb->get_results("SELECT p.post_author, p.ID, p.guid, p.post_modified, u.user_email 
  FROM $post_tb p INNER JOIN $user_tb u ON p.post_author = u.ID WHERE p.post_type='post' AND p.ID = $post_id ORDER BY ID ASC");
  //set post details
  if($wpdb->num_rows>0){
    foreach($posts as $post){
        $post_data = [
          'user_id' =>       $post->post_author,
          'user_email'=>     $post->user_email,
          'base_url'=>       $base_url,
          'post_id' =>       $post->ID,
          'post_link' =>     $post->guid,
          'post_modified' => $post->post_modified
        ];
    }
  }
  //set api request config
  $data_push_to_api = json_encode($post_data);
  $url = 'https://dashboard.sg-webdesign.net/wpupdate/'.$post_id;
  $arguments = array(
    'method' => 'POST',
    'headers' => array(
      'Content-Type' => 'application/json',
      'XSRF-TOKEN' => k2vw0u1hib9i0vTx5XGGO142n675oT3N2mSu96KI
    ),
    'sslverify' => false,
    'body' => $data_push_to_api,
  );
  //execute api request
  $response = wp_remote_post($url, $arguments);
  //var_dump($response);
}