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

  //main function for license check and credentials user page
function license_credentials_page(){
    global $wpdb;
    $license_tbl = $wpdb->prefix . 'license_check'; //table name
    $base_url = get_site_url();
    if(isset($_POST['keysubmit'])){     //if user submits license key
        if(empty($_POST['userkey'])){   //empty input validation
            echo "
            <script>alert('Please enter license key!');</script>
            <script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>
            ";
        }else{
            $base_url = get_site_url();
            $license_key = $_POST['userkey'];
            $send_license_check = array(    //JSON dataset to be sent to main database
                'base_url' => $base_url,
                'license_key' => $license_key,
              );
            $data_push_to_api = json_encode($send_license_check); //encode to JSON
            //execute laravel API request for license check
            $send_license = wp_remote_post('https://dashboard.sg-webdesign.net/licensevalidation', array(
                'method' => 'POST',
                'headers' => array(
                  'Content-Type' => 'application/json'
                ),
                  'sslverify' => false,
                  'body' => $data_push_to_api,
                )); 
            //wp_remote_retrieve_response_code
            $result = wp_remote_retrieve_body($send_license); //get response
            //if reponse is succesful save license key and activate plugin
            if($result == "Successful"){
                $wpdb->query("UPDATE $license_tbl SET license_type='Regular', license_key='$license_key' WHERE id=1");
                echo "
                    <script>alert('Thank you for your patronage!');</script>
                    <script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>
                    ";
            //else return invalid
            }elseif($result == "Invalid Key"){
                echo "
                    <script>alert('Please enter a valid license key!');</script>
                    <script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>
                    ";
            }else{
              echo "
                    <script>alert('This wordpress site is disabled! Please contact your administrator!');</script>
                    <script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>
                    ";
            }
        }
    }
    ?>
    <!-- MAIN FRONT END FOR LICENSE ACTIVATION USER INPUT -->
    <div style="position:relative; top: 20px; left: 20px; background-color: lightblue; text-align: center; width: 60%; padding: 30px; border-radius: 10px;" class="wrap">
        <form action="" method="post">
            <h1 style="font-weight: 500; font-size: 3rem;">ELEMENTOR TODO LIST PLUGIN</h1><br><br>
            <label style="font-size: 1.7rem;">Activation License Key Required</label><br>
            <h2 style="font-weight: 500; font-size: 1.2rem;"><?php echo "Site Domain: ".$base_url; ?></h2>
            <input style="width: 450px; padding: 0.3rem; margin-left: 6.4rem; font-size: 1.1rem;" type="text" id="userkey" name="userkey" class="form-control" placeholder="Enter your activation key here">
            <input style="padding: 0.6rem; width: 100px; font-weight: 500; font-size: 1rem;" type="submit" id="keysubmit" name="keysubmit" value="Activate"><br><br>
        </form>
            <a href="#" style="font-weight: 500; font-size: 1rem;">Don't have a elementor todo list plugin license yet?</a>
    </div>
    <?php
}

// main license validation function - checking if plugin is activated
function license_validation(){
    global $wpdb;
    $table_name = $wpdb->prefix . "license_check";      // table name
    $base_url = get_site_url();                         // get site url
    $license_key = $wpdb->get_var("SELECT license_key FROM $table_name WHERE id = 1"); // check if plugin is already activated
    if($license_key != "N/A"){
        $send_license_check = array(    //JSON dataset to be sent to main database
            'base_url' => $base_url,
            'license_key' => $license_key,
          );
        $data_push_to_api = json_encode($send_license_check); // encode array data to json
        $send_license = wp_remote_post('https://dashboard.sg-webdesign.net/licensecheck', array(
            'method' => 'POST',
            'headers' => array(
              'Content-Type' => 'application/json'
            ),
              'sslverify' => false,
              'body' => $data_push_to_api,
            ));
        $result = wp_remote_retrieve_body($send_license); // get api request response
        if($result=="Activated"){
             return true;
        }else{
             return false;
        }
    }else{
      return false;
    }
}