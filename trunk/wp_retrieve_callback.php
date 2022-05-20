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


function laravel_app_callback_endpoint(){
    register_rest_route(
        'laravelnotif/v1/', //Namespace
        'receive-callback', //Endpoint
        array(
            'methods'  => 'POST',
            'callback' => 'laravelnotif_receive_callback'
        )
    );
}

function laravelnotif_receive_callback($request_data){
    global $wpdb;
    $notif_tbl = $wpdb->prefix . 'laravel_data_notif_tbl';
    date_default_timezone_set('Asia/Singapore');
    $date = date('Y-m-d H:i:s');
    $parameters = $request_data->get_params();
    $data = array();
    $message = $parameters['message'];
    $wpdb->query("INSERT INTO $notif_tbl(notification,status,date_added) VALUES('$message',1,'$date')");

    $data['status'] = 'OK';
    $data['received_data'] = array(
        'message' => $message,
    );
    $data['message'] = 'You have reached the server.';
    return $data;
}

// require_once( __DIR__ . '/license-validation.php');
// if(license_validation()){
//         add_action('admin_notices','custom_admin_notice_popup');
// }

//main admin notice custom pop up
function custom_admin_notice_popup(){
    global $wpdb;
    $notif_tbl = $wpdb->prefix . 'laravel_data_notif_tbl'; //tbl name
    $result = $wpdb->get_results("SELECT * FROM $notif_tbl where status=1"); //get all messages sent from laravel
    //$current_url="//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $current_url = get_site_url();
    $current_url .= '/wp-admin/index.php'; 
    $redirect_url = 'http://';
    $redirect_url .= $_SERVER['HTTP_HOST']; // Get host
    $path = explode( '?', $_SERVER['REQUEST_URI'] ); // Blow up URI
    $redirect_url .= $path[0];
    //if notice is dismissed
    if (isset($_GET['dismiss-notice'])) {
        $id = $_GET['dismiss-notice'];
        $wpdb->query("UPDATE $notif_tbl SET status=0 WHERE id=$id"); //set status = 0
        echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>"; //redirect
    }
    //if there are msgs from laravel
    if(!empty($result)){
        foreach($result as $data){ //display each msg
            ?>
            <div class="notice notice-success">
                <p><?php _e('You have a new message ("'.$data->notification.'") from Todolist laravel!', 'elementor-todolist'); 
                echo '<a style="position:relative; left: 20px;" href="admin.php?page=elementor-todolist%2Ftodolist.php&dismiss-notice='.$data->id.'">Dismiss</a>';
            ?></p>
            </div>
            <?php
        }
    }
}


?>