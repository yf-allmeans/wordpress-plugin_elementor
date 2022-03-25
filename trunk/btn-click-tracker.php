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

//CLICK COUNTER AREA ################

// add_action( 'wp_enqueue_scripts', 'my_enqueue_function' );

// function my_enqueue_function() { 
//     //Option 1: Manually enqueue the wp-util library.
//     wp_enqueue_script( 'wp-util' );
//     // Option 2: Make wp-util a dependency of your script (usually better).
//     //wp_enqueue_script( 'btn_click', [ 'wp-util' ] );
// }

//add_action('admin_menu', 'send_btn_count');
register_activation_hook( __FILE__, 'send_btn_count');
add_action( 'wp_ajax_btn_check_click_counter', 'btn_check_click_counter');
add_action( 'wp_ajax_nopriv_btn_check_click_counter', 'btn_check_click_counter' );
add_action( 'wp_footer', 'btn_click' );

function send_btn_count(){
  global $wpdb;
  $base_url = get_site_url();
  $btn_table = $wpdb->prefix . 'get_btn_count';
  $post_data = array(
    'base_url' =>      $base_url,
  );
  //clear existing counts first
  $data_push_to_api = json_encode($post_data);
  $truncate = wp_remote_post('https://dashboard.sg-webdesign.net/truncatebtn', array(
    'method' => 'POST',
    'headers' => array(
      'Content-Type' => 'application/json',
    ),
    'body' => $data_push_to_api,
  )); 

  //get all subs
  $datasend = array();
  $results = $wpdb->get_results("SELECT * FROM $btn_table ORDER BY ID ASC");
  if($wpdb->num_rows>0){
    foreach($results as $result){
      //configure data to send
      $post_data = [
      'btn_id' =>     $result->btn_id,
      'post_id' =>    $result->post_id,
      'base_url' =>   $result->base_url,
      'post_link' =>  $result->post_link,
      'count' =>      $result->count,
      'user_ip' =>    $result->user_ip,
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
  //var_dump($response);

}


function btn_check_click_counter() {
    if ( isset( $_POST['nonce'] ) &&  isset( $_POST['post_id'] ) && wp_verify_nonce( $_POST['nonce'], 'btn_check_click_counter_' . $_POST['post_id'] )) {
      global $wpdb;
      $base_url = get_site_url();
      $btn_id = $_POST['btn_id'];
      $post_id = $_POST['post_id'];
      $user_ip = $_POST['user_ip'];
      $btn_table = $wpdb->prefix . 'get_btn_count';
      $posts_table = $wpdb->prefix . 'posts';
      $post_link = $wpdb->get_var("SELECT guid FROM $posts_table WHERE ID='$post_id'");
      $existcount = "SELECT * FROM $btn_table 
                                   WHERE btn_id = '$btn_id' AND post_id = '$post_id' AND base_url = '$base_url' AND user_ip = '$user_ip'";
      $existing = $wpdb->get_results($existcount);
      if($existing){
          $update_click_record = $wpdb->get_results("UPDATE $btn_table SET count=count+1
                                   WHERE btn_id = '$btn_id' AND post_id = '$post_id' AND base_url = '$base_url' AND user_ip = '$user_ip'");
      }
      else{
          $new_click_record = $wpdb->get_results("INSERT INTO $btn_table(btn_id, post_id, base_url, post_link, count, user_ip)
                                                  VALUES('$btn_id','$post_id','$base_url','$post_link',1,'$user_ip')");
      }
      //set api request config
      $post_data = array(
        'btn_id' => $btn_id,
        'post_id' => $post_id,
        'base_url' => $base_url,
        'post_link' => $post_link,
        'user_ip' => $user_ip,
      );
      $data_push_to_api = json_encode($post_data);
        $data_push_to_api = json_encode($post_data);
        $url = 'https://dashboard.sg-webdesign.net/savebtn';
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
        //$count = get_post_meta( $_POST['post_id'], 'btn_check_click_counter', true );
        //update_post_meta( $_POST['post_id'], 'btn_check_click_counter', ( $count === '' ? 1 : $count + 1 ) );
    }
    exit();
}

function btn_click() {
    global $post;
    $user_ip = $_SERVER['REMOTE_ADDR'];
    if( isset( $post->ID ) ) {
?>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script type="text/javascript" >
    jQuery(function ($) {
        $( '.elementor-button-wrapper' ).on( 'click', 'a', function() {
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
            btn_id: btnid,
            };
            //temporary display
            var oldval = parseInt($('#counterdd').text());
            $('#counterdd').html(oldval+1);
            $.post( ajax_options.ajaxurl, ajax_options, function() {
                redirectWindow.location;
            });
            return false;
        });
    });
    </script>
<?php
    echo "<p id='counterdd'>".get_post_meta($post->ID,'btn_check_click_counter',true)."</p>";
    }
}
