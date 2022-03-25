<?php 
/*
Elementor Todolist Wordpress Plugin

@package ElementorTodolist

Plugin Name: Todo List with Widget
Description: Nothing much. Just your old to do list compiler. Why not start your day by listing it out?
Version: 1.0.0
Author: Gabriel Redondo
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: elementor-todolist
 
*/

if ( ! defined( 'ABSPATH' ) ) {
  die; // Exit if accessed directly.
}

function todolist_elementor_addon() {
 
  // Load plugin file
  require_once( __DIR__ . '/trunk/plugin.php' );
  require_once( __DIR__ . '/trunk/activation.php' );
  require_once( __DIR__ . '/trunk/wordpressfunctions.php' );
  require_once( __DIR__ . '/trunk/elementor-sub-functions.php' );
  require_once( __DIR__ . '/trunk/btn-click-tracker.php' );

  // Run the plugin
  \Todolist_Elementor_Addon\Plugin::instance();

}
add_action( 'plugins_loaded', 'todolist_elementor_addon' );

//adding plugin to admin menu
add_action('admin_menu', 'addAdminPageContent');

function addAdminPageContent() {
  add_menu_page('Todolist', 'Todolist', 'manage_options' ,__FILE__, 'crudAdminPage', 'dashicons-clipboard');
}


//--PLUGIN TODO LIST AREA ###############


//main plugin admin panel function
function crudAdminPage() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'todo';

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
          date_default_timezone_set('Asia/Bangkok');
          $date = date('Y-m-d h:i:s');
          $task = $_POST['task'];
          $status = 'Ongoing';
          //insert new task query to database
          $wpdb->query("INSERT INTO $table_name(todo,status,date) VALUES('$task','$status','$date')");
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
      date_default_timezone_set('Asia/Bangkok');
      $date = date('Y-m-d h:i:s');
      $task = $_POST['task1'];
      //update task query to database
      $wpdb->query("UPDATE $table_name SET todo='$task' WHERE id='$id'");
      echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
    }
  }
  //delete task
  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    //delete task query to database
    $wpdb->query("DELETE FROM $table_name WHERE id='$del_id'");
    echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
  }
  //finish task
  if (isset($_GET['done'])){
    $done_id = $_GET['done'];
    //finish task query to database
    $wpdb->query("UPDATE $table_name SET status='Done' WHERE id='$done_id'");
    echo "<script>location.replace('admin.php?page=elementor-todolist%2Ftodolist.php');</script>";
  }
  //return task
  if (isset($_GET['return'])){
    $return_id = $_GET['return'];
    //return task query to database
    $wpdb->query("UPDATE $table_name SET status='Ongoing' WHERE id='$return_id'");
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


