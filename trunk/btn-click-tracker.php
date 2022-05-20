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

//CLICK COUNTER AREA ################

if ( ! defined( 'ABSPATH' ) ) {
  die; // Exit if accessed directly.
}

//add_action('admin_menu', 'send_btn_count');

//add ajax function to button check click counter to initialize ajax function upon calls
add_action( 'wp_ajax_btn_check_click_counter', 'btn_check_click_counter');
add_action( 'wp_ajax_nopriv_btn_check_click_counter', 'btn_check_click_counter' );
//add function to wp footer on every page
add_action( 'wp_footer', 'btn_click' );

//sending current records to laravel api database
function send_btn_count(){
  global $wpdb;
  $base_url = get_site_url();
  $btn_table = $wpdb->prefix . 'get_btn_count';
  $tracked_btnlist_table = $wpdb->prefix . 'btn_track_list';
  //post data (conditional factor for truncate)
  $post_data = array(
    'base_url' =>      $base_url,
  );
  //clear existing counts in laravel first to refresh data upon activation
  $data_push_to_api = json_encode($post_data);
  //execute post
  $truncate = wp_remote_post('https://dashboard.sg-webdesign.net/truncatebtn', array(
    'method' => 'POST',
    'headers' => array(
      'Content-Type' => 'application/json',
    ),
    'body' => $data_push_to_api,
  )); 
  //get all click count in wordpress
  $datasend = array();
  $datasend2 = array();
  $results = $wpdb->get_results("SELECT * FROM $btn_table ORDER BY ID ASC");
  $results2 = $wpdb->get_results("SELECT * FROM $tracked_btnlist_table ORDER BY ID ASC");
  //retrieve existing button click count
  if(!empty($results)){
    foreach($results as $result){
      //configure data to send
      $post_data = [
      'btn_id' =>     $result->btn_id,
      'post_id' =>    $result->post_id,
      'base_url' =>   $result->base_url,
      'post_link' =>  $result->post_link,
      'user_ip' =>    $result->user_ip,
      'date_added' => $result->date_added,
      ];
      array_push($datasend, $post_data);
    }
    // post to laravel api then store to its database
    $data_push_to_api = json_encode($datasend);
    $url = 'https://dashboard.sg-webdesign.net/retrievebtn';
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
  //retrieve tracked button list
  if(!empty($results2)){
    foreach($results2 as $result2){
      //configure data to send
      $post_data2 = [
      'domain_id' =>  $result2->id,
      'btn_id' =>     $result2->btn_id,
      'base_url' =>   $result2->base_url,
      'status' =>     $result2->status,
      'date_added' => $result2->date_added,
      ];
      array_push($datasend2, $post_data2);
    }
    // post to laravel api then store to its database
    $data_push_to_api2 = json_encode($datasend2);
    $url2 = 'https://dashboard.sg-webdesign.net/retrievebtnlist';
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
  //var_dump($response);

}

//btn click counter main function php backend
function btn_check_click_counter() {
    //check if post data contains nonce, post id and verify
    if ( isset( $_POST['nonce'] ) &&  isset( $_POST['post_id'] ) && wp_verify_nonce( $_POST['nonce'], 'btn_check_click_counter_' . $_POST['post_id'] )) {
      global $wpdb;
      date_default_timezone_set('Asia/Singapore');
      $date = date('Y-m-d H:i:s');
      $base_url = get_site_url();   //get url
      $btn_id = $_POST['btn_id'];   //get button id from post
      $post_id = $_POST['post_id']; //get post id from post
      $user_ip = $_POST['user_ip']; //get user ip from post
      $btn_table = $wpdb->prefix . 'get_btn_count'; //define btn table name
      $posts_table = $wpdb->prefix . 'posts'; //define posts table name
      //$post_link = $wpdb->get_var("SELECT guid FROM $posts_table WHERE ID='$post_id'"); //get post permalink
      $post_link = $_POST['post_link'];
      //check if btn already has record under a user ip
      $existcount = "SELECT * FROM $btn_table 
                                   WHERE btn_id = '$btn_id' AND post_id = '$post_id' AND base_url = '$base_url' AND user_ip = '$user_ip'";
      //execute
      $existing = $wpdb->get_results($existcount);
      //update data and add count only if already existing record
      // if($existing){
      //     $update_click_record = $wpdb->get_results("UPDATE $btn_table SET count=count+1
      //                              WHERE btn_id = '$btn_id' AND post_id = '$post_id' AND base_url = '$base_url' AND user_ip = '$user_ip'");
      // }//add new record if not existing
      // else{
      $new_click_record = $wpdb->get_results("INSERT INTO $btn_table(btn_id, post_id, base_url, post_link, user_ip, date_added)
                                                  VALUES('$btn_id','$post_id','$base_url','$post_link','$user_ip','$date')");
      //}
      //set api request config to send to laravel api
      $post_data = array(
        'btn_id' => $btn_id,
        'post_id' => $post_id,
        'base_url' => $base_url,
        'post_link' => $post_link,
        'user_ip' => $user_ip,
        'date_added' => $date,
      );
        $data_push_to_api = json_encode($post_data); //encode data
        $url = 'https://dashboard.sg-webdesign.net/savebtn'; //set api url
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
    exit();
}

//function button click via ajax
function btn_click() {
    global $post, $wpdb;
    $user_ip = $_SERVER['REMOTE_ADDR']; //get user up
    $tbl = $wpdb->prefix . 'btn_track_list'; //define btn table 
    // $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
    // $url = $base_url . $_SERVER["REQUEST_URI"];
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $tracked_btn = $wpdb->get_col("SELECT btn_id FROM $tbl WHERE status='Activated'"); //check if activated
    $btn_all = preg_filter('/^/', '#', $tracked_btn); //add # prefix to all records in the array of activated buttons to make them IDs
    $tracked_final = implode(', ', $btn_all); //separate each array item them with commas
    //if current page contains post id and activated buttons
    if( isset( $post->ID ) and $tracked_btn) {
?>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script type="text/javascript" >
    //click function
    jQuery(function ($) {
        //execute for all activated buttons
        $( document ).on( 'click', '<?php echo $tracked_final ?>', function() {
            var href = $( this ).attr( "href" );
            var redirectWindow = window.open(href, '_blank');
            //if the button has no id, get the text of its child element, else get the text of grandchild element
            if($( this ).attr( "id" )){
              var btnid = $(this).attr("id");
            }else{
              if($( this ).children().text()){
                var btnid = ($( this ).children().text());
              }else{
                if($( this ).children().children().text()){
                  var btnid = $( this ).children().children().text();
                }
              }
            }
            //set ajax parameters to be passed
            var ajax_options = {
                action: 'btn_check_click_counter',
                nonce: '<?php echo wp_create_nonce( 'btn_check_click_counter_' . $post->ID ); ?>',
                ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                post_id: '<?php echo $post->ID; ?>',
                user_ip: '<?php echo $user_ip; ?>',
                post_link: '<?php echo $url ?>' + window.location.hash,
                btn_id: btnid,
                };

            $.post( ajax_options.ajaxurl, ajax_options, function() {
                redirectWindow.location;
            });
            return false;
        });
    });
    </script>
<?php
    //echo "<p id='counterdd'>". $tracked_final ."</p>";
    }
}
