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

function button_tracker_list_tab(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'btn_track_list';
    $count_tbl = $wpdb->prefix . 'get_btn_count';
    //new button track
    if (isset($_POST['newbtn'])) {
        //empty input user validation
        if(empty($_POST['btn'])){
          echo "
          <script>alert('Fields are required!');</script>
          <script>location.replace('admin.php?page=button_tracker_tab');</script>
          ";
        }
        else{
        //get post data and save to database
              date_default_timezone_set('Asia/Singapore');
              $date = date('Y-m-d H:i:s');
              $btn_id = $_POST['btn'];
              $base_url = get_site_url();
              $status = 'Activated';
              //insert new task query to database
              $wpdb->query("INSERT INTO $table_name(btn_id,base_url,status,date_added) VALUES('$btn_id','$base_url','$status','$date')");
              $data = $wpdb->get_row("SELECT * FROM $table_name ORDER BY ID DESC LIMIT 1");
              $post_data = array(
                'domain_id'     => $data->id,
                'btn_id'        => $data->btn_id,
                'base_url'      => $base_url,
                'status'        => $data->status,
                'date_added'    => $data->date_added,
              );
                $data_push_to_api = json_encode($post_data); //encode data
                $url = 'https://dashboard.sg-webdesign.net/newtrackbtn'; //set api url
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

              echo "<script>location.replace('admin.php?page=button_tracker_tab');</script>";
        }
      }
      //update button ID
      if (isset($_POST['upbtn'])) {
        //empty input user validation
        if(empty($_POST['btn1'])){
          echo "
          <script>alert('Fields are required!');</script>
          ";
        }
        else{
          //get post data and update
          $id = $_POST['id'];
          $btn_id = $_POST['btn1'];
          //update task query to database
          $wpdb->query("UPDATE $table_name SET btn_id='$btn_id' WHERE id='$id'");

          $data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$id'");
          $post_data = array(
            'btn_id'        => $data->btn_id,
            'base_url'      => $data->base_url,
            'status'        => $data->status,
          );
            $data_push_to_api = json_encode($post_data); //encode data
            $url = 'https://dashboard.sg-webdesign.net/updatetrackbtn/'.$data->id; //set api url
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
            echo "<script>location.replace('admin.php?page=button_tracker_tab');</script>";
        }
      }
      //delete button ID record
      if (isset($_GET['delbtn'])) {
        $del_id = $_GET['delbtn'];
        //delete to laravel
        $data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$del_id'");
          $post_data = array(
            'base_url'      => $data->base_url,
          );
            $data_push_to_api = json_encode($post_data); //encode data
            $url = 'https://dashboard.sg-webdesign.net/deltrackbtn/'.$data->id; //set api url
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
        //delete query to database
        $wpdb->query("DELETE FROM $table_name WHERE id='$del_id'");
        echo "<script>location.replace('admin.php?page=button_tracker_tab');</script>";
      }
      //deactivate button
      if (isset($_GET['deacbtn'])) {
        $deac_id = $_GET['deacbtn'];
        $status = 'Deactivated';
        //set status to deactivated
        $wpdb->query("UPDATE $table_name SET status = '$status' WHERE id='$deac_id'");
        $data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$deac_id'");
          $post_data = array(
            'btn_id'        => $data->btn_id,
            'base_url'      => $data->base_url,
            'status'        => $data->status,
          );
            $data_push_to_api = json_encode($post_data); //encode data
            $url = 'https://dashboard.sg-webdesign.net/updatetrackbtn/'.$data->id; //set api url
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
        echo "<script>location.replace('admin.php?page=button_tracker_tab');</script>";
      }
      //activate button
      if (isset($_GET['actbtn'])) {
        $act_id = $_GET['actbtn'];
        $status = 'Activated';
        //set status to activated
        $wpdb->query("UPDATE $table_name SET status = '$status' WHERE id='$act_id'");
        $data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$act_id'");
          $post_data = array(
            'btn_id'        => $data->btn_id,
            'base_url'      => $data->base_url,
            'status'        => $data->status,
          );
            $data_push_to_api = json_encode($post_data); //encode data
            $url = 'https://dashboard.sg-webdesign.net/updatetrackbtn/'.$data->id; //set api url
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
        echo "<script>location.replace('admin.php?page=button_tracker_tab');</script>";
      }
    ?>
    <div class="wrap">
      <!-- add new button to track -->
      <h2>New Track Button</h2>
      <table class="wp-list-table widefat striped">
        <thead>
          <tr>
            <!-- table labels for new button id input -->
            <th width="33%">ID</th>
            <th width="33%">Button ID</th>
            <th width="33%">Action</th>
          </tr>
        </thead>
        <tbody>
          <form action="" method="post">
            <tr>
              <!-- add new button id form -->
              <td><input type="text" value="AUTO_GENERATED" disabled></td>
              <td><input type="text" id="btn" name="btn" placeholder="Enter Button ID"></td>
              <td><button id="newbtn" name="newbtn" type="submit">INSERT</button></td>
            </tr>
          </form>
          </tbody> 
          </table>
          <!-- ongoing button track table -->
          <h1>Currently Tracked Buttons</h1>
          <table class="wp-list-table widefat striped">
          <thead>
                <tr>
                  <!-- ongoing button track header -->
                  <th width="20%">ID</th>
                  <th width="20%">Button</th>
                  <th width="20%">Status</th>
                  <th width="20%">Date Added</th>
                  <th width="20%">Action</th>
                </tr>
          </thead>
          <tbody>
          <?php
            //$result = $wpdb->get_results("SELECT b.id, b.btn_id, b.base_url, b.status, b.date_added, SUM(c.count) as cnt 
            //FROM $table_name b JOIN $count_tbl c ON b.btn_id = c.btn_id WHERE b.status = 'Activated' GROUP BY b.btn_id");

          //database query to call currently tracked buttons 
          //query and call activated buttons
           $result = $wpdb->get_results("SELECT * FROM $table_name WHERE status='Activated'");  
           // loop called data to display into rows
            foreach ($result as $print) {
              echo "
                <tr>
                <form action='' method='post'>
                  <td width='20%'>$print->id</td>
                  <td width='20%'>$print->btn_id</td>
                  <td width='20%'>$print->status</td>
                  <td width='20%'>$print->date_added</td>
                  <td width='20%'>
                  <a href='admin.php?page=button_tracker_tab&upbtn=$print->id'><button type='button'>UPDATE</button></a> 
                  <a href='admin.php?page=button_tracker_tab&delbtn=$print->id'><button type='button'>DELETE</button></a>  
                  <a href='admin.php?page=button_tracker_tab&deacbtn=$print->id'><button type='button'>DEACTIVATE</button></a>  
                  </td>
                </tr>
                </form>
              ";
            }
          ?>
          </tbody>
          </table>
          <!--Deactivated buttons -->
          <h1>Track Deactivated Buttons</h1>
          <table class="wp-list-table widefat striped">
          <thead>
                <tr>
                  <!-- deactivated buttons table header -->
                  <th width="20%">ID</th>
                  <th width="20%">Button</th>
                  <th width="20%">Status</th>
                  <th width="20%">Date Added</th>
                  <th width="20%">Action</th>
                </tr>
          </thead>
          <tbody>
          <?php
            //$result = $wpdb->get_results("SELECT b.id, b.btn_id, b.base_url, b.status, b.date_added, SUM(c.count) as cnt 
            //FROM $table_name b JOIN $count_tbl c ON b.btn_id = c.btn_id WHERE b.status = 'Deactivated' GROUP BY b.btn_id");

            //database query to call currently tracked buttons
            //query and call deactivated buttons
            $result = $wpdb->get_results("SELECT * FROM $table_name WHERE status='Deactivated'");                              
            //loop called data to display into rows
            foreach ($result as $print) {
              echo "
                <tr>
                <form action='' method='post'>
                  <td width='20%'>$print->id</td>
                  <td width='20%'>$print->btn_id</td>
                  <td width='20%'>$print->status</td>
                  <td width='20%'>$print->date_added</td>
                  <td width='20%'>
                  <a href='admin.php?page=button_tracker_tab&upbtn=$print->id'><button type='button'>UPDATE</button></a> 
                  <a href='admin.php?page=button_tracker_tab&delbtn=$print->id'><button type='button'>DELETE</button></a>  
                  <a href='admin.php?page=button_tracker_tab&actbtn=$print->id'><button type='button'>ACTIVATE</button></a>  
                  </td>
                </tr>
                </form>
              ";
            }
          ?>
          </tbody>
          </table>
<?php
        //edit button ID form display
          if (isset($_GET['upbtn'])) {
            $upt_id = $_GET['upbtn'];
            //get button information to fill fieldss
            $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$upt_id'");
            foreach($result as $print) {
              $btn_id = $print->btn_id;
              $status = $print->status;
            }
            //edit button ID form display 
            echo "
            <table class='wp-list-table widefat striped'>
            <h1>Edit Button ID<h1>
              <thead>
                <tr>
                  <th width='33%'>ID</th>
                  <th width='33%'>Button ID</th>
                  <th width='33%'>Actions</th>
                </tr>
              </thead>
              <tbody>
                <form action='' method='post'>
                  <tr>
                    <td width='33%'>$print->id <input type='hidden' id='id' name='id' value='$print->id'></td>
                    <td width='33%'><input type='text' id='btn1' name='btn1' value='$print->btn_id'></td>
                    <td width='33%'><button id='upbtn' name='upbtn' type='submit'>SAVE</button>
                    <a href='admin.php?page=button_tracker_tab'><button type='button'>CANCEL</button></a></td>
                  </tr>
                </form>
              </tbody>
            </table>";
          }
}