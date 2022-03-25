<?php
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