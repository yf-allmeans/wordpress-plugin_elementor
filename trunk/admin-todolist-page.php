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
  
 //--PLUGIN TODO LIST AREA ###############
//main plugin admin panel function
function crudAdminPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'todo';
    $base_url = get_site_url();
    date_default_timezone_set('Asia/Singapore');
  //new task
    if (isset($_POST['newsubmit'])) {
      //empty input user validation
      if(empty($_POST['task'])){
        echo "
        <script>alert('Fields are required!');</script>
        <script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>
        ";
      }
      else{
            $date = date('Y-m-d H:i:s');
            $task = $_POST['task'];
            $status = 'Ongoing';
            //insert new task query to database
            $wpdb->query("INSERT INTO $table_name(todo,status,date) VALUES('$task','$status','$date')");
            //send new data to the laravel end
            $result = $wpdb->get_row("SELECT * FROM $table_name ORDER BY ID DESC LIMIT 1");
            $post_data = [
              'wp_id' =>      $result->id,
              'base_url' =>   $base_url,
              'todo' =>       $result->todo,
              'status' =>     $result->status,
              'date_added' => $result->date,
            ];
            $data_push_to_api = json_encode($post_data);
            $url = 'https://dashboard.sg-webdesign.net/tasksendnew'; //set api url
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
            echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
      }
    }
  //update task
    if (isset($_POST['uptsubmit'])) {
      //empty input user validation
      if(empty($_POST['task1'])){
        echo "
        <script>alert('Fields are required!');</script>
        ";
      }
      else{
        $id = $_POST['id'];
        $task = $_POST['task1'];
        //update task query to database
        $wpdb->query("UPDATE $table_name SET todo='$task' WHERE id='$id'");
        $result = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$id'");
        $post_data = [
          'base_url' =>   $base_url,
          'todo' =>       $result->todo,
          'status' =>     $result->status,
        ];
        $data_push_to_api = json_encode($post_data);
        $url = 'https://dashboard.sg-webdesign.net/tasksendupdate/'.$id; //set api url
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
        echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
      }
    }
    //delete task
    if (isset($_GET['del'])) {
      $del_id = $_GET['del'];
      //delete task query to database
        $post_data = [
          'base_url' =>   $base_url,
        ];
        $data_push_to_api = json_encode($post_data);
        $url = 'https://dashboard.sg-webdesign.net/tasksenddelete/'.$del_id; //set api url
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
      $wpdb->query("DELETE FROM $table_name WHERE id='$del_id'");
      echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
    }
    //finish task
    if (isset($_GET['done'])){
      $done_id = $_GET['done'];
      //finish task query to database
      $wpdb->query("UPDATE $table_name SET status='Done' WHERE id='$done_id'");
      //send update to laravel
      $result = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$done_id'");
        $post_data = [
          'base_url' =>   $base_url,
          'todo' =>       $result->todo,
          'status' =>     $result->status,
        ];
        $data_push_to_api = json_encode($post_data);
        $url = 'https://dashboard.sg-webdesign.net/tasksendupdate/'.$done_id; //set api url
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
      echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
    }
    //return task
    if (isset($_GET['return'])){
      $return_id = $_GET['return'];
      //return task query to database
      $wpdb->query("UPDATE $table_name SET status='Ongoing' WHERE id='$return_id'");
      //send update to laravel
      $result = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$return_id'");
        $post_data = [
          'base_url' =>   $base_url,
          'todo' =>       $result->todo,
          'status' =>     $result->status,
        ];
        $data_push_to_api = json_encode($post_data);
        $url = 'https://dashboard.sg-webdesign.net/tasksendupdate/'.$return_id; //set api url
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
      echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
    }
  
    ?>
    <div class="wrap">
      <!-- add new task -->
      <h2>Add new task</h2>
      <table class="wp-list-table widefat striped">
        <thead>
          <tr>
            <th width="33%">Task ID</th>
            <th width="33%">Task</th>
            <th width="33%">Action</th>
          </tr>
        </thead>
        <tbody>
          <form action="" method="post">
            <tr>
              <!-- add new task form -->
              <td><input type="text" value="AUTO_GENERATED" disabled></td>
              <td><input type="text" id="task" name="task" placeholder="Enter task name"></td>
              <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
            </tr>
          </form>
          </tbody> 
          </table>
          <!-- ongoing tasks table -->
          <h1>To do list</h1>
          <table class="wp-list-table widefat striped">
          <thead>
                <tr>
                  <!-- ongoing tasks table header -->
                  <th width="20%">Task ID</th>
                  <th width="20%">Task</th>
                  <th width="20%">Status</th>
                  <th width="20%">Date</th>
                  <th width="20%">Action</th>
                </tr>
          </thead>
          <tbody>
          <?php
          // database query to call ongoing tasks
            $result = $wpdb->get_results("SELECT * FROM $table_name where status<>'Done'");
            // loop called data to display into rows
            foreach ($result as $print) {
              echo "
                <tr>
                <form action='' method='post'>
                  <td width='20%'>$print->id</td>
                  <td width='20%'>$print->todo</td>
                  <td width='20%'>$print->status</td>
                  <td width='20%'>$print->date</td>
                  <td width='20%'><a href='admin.php?page=elementor-todolist%2Ftodolist.php&upt=$print->id'><button type='button'>UPDATE</button></a> 
                  <a href='admin.php?page=elementor-todolist%2Ftodolist.php&done=$print->id'><button type='button'>DONE</button></a>
                  <a href='admin.php?page=elementor-todolist%2Ftodolist.php&del=$print->id'><button type='button'>DELETE</button></a>  
                  </td>
                </tr>
                </form>
              ";
            }
          ?>
          </tbody>
          </table>
          <!-- finished tasks table -->
          <h1>Finished Tasks</h1>
          <table class="wp-list-table widefat striped">
          <thead>
                <tr>
                  <!-- finished tasks table header -->
                  <th width="20%">Task ID</th>
                  <th width="20%">Task</th>
                  <th width="20%">Status</th>
                  <th width="20%">Date</th>
                  <th width="20%">Action</th>
                </tr>
          </thead>
          <tbody>
          <?php
            // database query to call finished tasks data
            $result = $wpdb->get_results("SELECT * FROM $table_name where status='Done'");
            // loop called data to display into rows
            foreach ($result as $print) {
              echo "
                <tr>
                <form action='' method='post'>
                  <td width='20%'>$print->id</td>
                  <td width='20%'>$print->todo</td>
                  <td width='20%'>$print->status</td>
                  <td width='20%'>$print->date</td>
                  <td width='20%'>
                  <a href='admin.php?page=elementor-todolist%2Ftodolist.php&return=$print->id'><button type='button'>RETURN</button></a>
                  <a href='admin.php?page=elementor-todolist%2Ftodolist.php&del=$print->id'><button type='button'>DELETE</button></a>  
                  </td>
                </tr>
                </form>
              ";
            }
          ?>
          </tbody>
          </table>
      <br>
      <br>
      <?php
      //update task display
        if (isset($_GET['upt'])) {
          $upt_id = $_GET['upt'];
          $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$upt_id'");
          foreach($result as $print) {
            $task = $print->todo;
            $status = $print->status;
          }
          // update task form
          echo "
          <table class='wp-list-table widefat striped'>
          <h1>Edit Task<h1>
            <thead>
              <tr>
                <th width='33%'>Task ID</th>
                <th width='33%'>Task</th>
                <th width='33%'>Actions</th>
              </tr>
            </thead>
            <tbody>
              <form action='' method='post'>
                <tr>
                  <td width='33%'>$print->id <input type='hidden' id='id' name='id' value='$print->id'></td>
                  <td width='33%'><input type='text' id='task1' name='task1' value='$print->todo'></td>
                  <td width='33%'><button id='uptsubmit' name='uptsubmit' type='submit'>SAVE</button>
                  <a href='admin.php?page=elementor-todolist%2Ftodolist.php'><button type='button'>CANCEL</button></a></td>
                </tr>
              </form>
            </tbody>
          </table>";
        }
      ?>
    </div>
    <?php
  }
  
  
  