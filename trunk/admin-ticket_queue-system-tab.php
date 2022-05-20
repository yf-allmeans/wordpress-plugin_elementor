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

//main function for ticket support admin submenu display
function ticket_support_tab(){
  global $wpdb;
  $table_name = $wpdb->prefix . 'ticket_system_tbl';
  $exchange_tbl_name = $wpdb->prefix . 'ticket_system_exchange';
  $license_tbl = $wpdb->prefix . 'license_check';
  date_default_timezone_set('Asia/Singapore');
  //get the current queue from laravel
  $active_ticket_check = $wpdb->get_var("SELECT count(*) FROM $table_name WHERE status<>'Closed'");
  $get_license_type = $wpdb->get_var("SELECT license_type FROM $license_tbl WHERE id = 1"); //get license type
  $current_queue_url = 'https://dashboard.sg-webdesign.net/ticketcurrentqueue/'.$get_license_type; //set api url
  $currentqueue = wp_remote_get($current_queue_url); //execute API request
  $next_in_queue = intval(wp_remote_retrieve_body($currentqueue)); //get response from laravel
  //main view dashboard
  if(!isset($_GET['viewticket']) && !isset($_GET['newticket']) && !isset($_GET['editticket'])){
  ?>
  <div class="container-fluid">
    <div class="col-lg-10">
          <div class="row">
              <div class="col"><h2 style="margin-top: 2%;">My Tickets</h2></div>
              <div class="col">
                  <a class="btn btn-primary" style="float: right; margin-top: 2%;" 
                  href='admin.php?page=ticket_queue_tab&newticket'
                  >
                  CREATE TICKET
                  </a>
              </div>
          </div>
<?php     if($active_ticket_check!=0){  ?>
              <div id="currentqueue">
                <ul class="list-group list-group-horizontal">
                    <li id="nowserving" class="list-group-item">Now Serving: #<?php echo $next_in_queue ?></li>
                    <li id="nextserving" class="list-group-item">Next in Queue: #<?php echo $next_in_queue+1 ?></li>
                </ul>
              </div>
<?php      }                            ?>
      <table class="table table-bordered">
          <thead>
                <tr class="table-dark">
                  <!-- ticket list header -->
                  <th style='text-align: center;' scope="col">ID</th>
                  <th style='text-align: center;' scope="col">Queue</th>
                  <th style='text-align: center;' scope="col">Subject</th>
                  <th style='text-align: center;' scope="col">Designation</th>
                  <th style='text-align: center;' scope="col">Status</th>
                  <th style='text-align: center;' scope="col">Last Modified</th>
                  <th style='text-align: center;' scope="col">Date Added</th>
                  <th style='text-align: center;' scope="col">Action</th>
                </tr>
          </thead>
          <tbody>
          <?php
          //list all tickets
            $result = $wpdb->get_results("SELECT * FROM $table_name ORDER BY last_modified DESC");  
            if(!empty($result)){//table rows
                  foreach ($result as $print) {
                    echo "
                      <tr>
                      <form action='' method='post'>
                        <td class='align-middle' style='text-align: center;'>$print->id</td>
                        <td class='align-middle' style='text-align: center;'>$print->queue</td>
                        <td class='align-middle' style='text-align: center;'>$print->subject</td>
                        <td class='align-middle' style='text-align: center;'>$print->task_type</td>
                        <td class='align-middle' style='text-align: center;'>$print->status</td>
                        <td class='align-middle' style='text-align: center;'>$print->last_modified</td>
                        <td class='align-middle' style='text-align: center;'>$print->date_added</td>
                        <td class='align-middle' style='text-align: center;'>";
                  if($print->status!="Closed"){
                    echo "
                        <a class='btn btn-success' href='admin.php?page=ticket_queue_tab&viewticket=$print->id'>VIEW</a> 
                        <a class='btn btn-warning' href='admin.php?page=ticket_queue_tab&editticket=$print->id'>EDIT</a> 
                        </td>
                      </tr>
                      </form>
                      ";
                  }else{
                    echo "
                        <a class='btn btn-success' href='admin.php?page=ticket_queue_tab&viewticket=$print->id'>VIEW</a>
                        </td>
                      </tr>
                      </form>
                      ";
                  } //if status open/closed
                  } // foreach
            } //if not empty result
          ?>
          </tbody>
      </table>
     </div><!-- col lg 10 -->
  </div>
  <?php
    }elseif(isset($_GET['newticket'])){//new ticket
    ?>
          <div class='col-lg-6' style='background: #F8F9FA; border: 1px solid black; border-radius: 5px; padding: 15px; margin-top: 2%;'>
              <div class='row'>
                    <div class='col'><h2>Create Ticket</h2></div>
                    <div class='col'><a style='float: right;' href='admin.php?page=ticket_queue_tab' class='btn btn-warning'>Cancel</a></div>
              </div>
              <form class='row-g-3 col-lg-12' action='' method='POST'>
                <div class='col-12'>
                    <label style='margin-top: 2%;' for='subject' class='form-label'>Subject</label>
                    <input type='text' class='form-control' id='ticketsubject' name='ticketsubject' placeholder='Enter Ticket Subject'>
                </div>
                <div class='col-12'>
                    <label style='margin-top: 2%;' for='tasktype' class='form-label'>Task Type</label>
                    <select class="form-select form-select-lg p-2 row-g-3" id="tasktype" name="tasktype">
                        <option selected="true" disabled="disabled">Select Task Type</option>    
                        <option value="Software Development">Software Development</option>
                        <option value="Digital Marketing">Digital Marketing</option>
                        <option value="Design">Design</option>
                    </select>
                </div>
                <div class='col-12'>
                    <label style='margin-top: 2%' for='ticketbody' class='form-label'>Content Body</label>
                    <textarea class='form-control' id='ticketbody' style='height: 10rem;' name='ticketbody' placeholder='Enter Concern here'></textarea>
                </div>
                <div class='col-12'>
                    <button type='submit' id='newticks' name='newticks' style='margin-top: 2%;' class='btn btn-primary'>Submit</button>
                </div>
              </form>
          </div>
  <?php
    }elseif(isset($_GET['editticket'])){//new ticket
      $tickid = $_GET['editticket'];
      $results = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$tickid'");
      $task_type = $results->task_type;
?>
          <div class='col-lg-6' style='background: #F8F9FA; border: 1px solid black; border-radius: 5px; padding: 15px; margin-top: 2%;'>
              <div class='row'>
                    <div class='col'><h2>Edit Ticket Details</h2></div>
                    <div class='col'><a style='float: right;' href='admin.php?page=ticket_queue_tab' class='btn btn-warning'>Cancel</a></div>
              </div>
              <form class='row-g-3 col-lg-12' action='' method='POST'>
                <div class='col-12'>
                    <label style='margin-top: 2%' for='subject' class='form-label'>Subject</label>
                    <input type='text' value="<?php echo $results->subject ?>" class='form-control' id='ticketsubject' name='ticketsubject' placeholder='Enter Ticket Subject'>
                </div>
                <div class='col-12'>
                    <label style='margin-top: 2%;' for='tasktype' class='form-label'>Task Type</label>
                    <select class="form-select form-select-lg p-2 row-g-3" id="tasktype" name="tasktype">
                        <option value="Software Development" <?php if ($task_type=="Software Development") echo "selected";?> >
                        Software Development</option>
                        <option value="Digital Marketing" <?php if ($task_type=="Digital Marketing") echo "selected";?> >
                        Digital Marketing</option>
                        <option value="Design" <?php if ($task_type=="Design") echo "selected";?>>
                        Design</option>
                    </select>
                </div>
                <div class='col-12'>
                    <label style='margin-top: 2%' for='ticketbody' class='form-label'>Content Body</label>
                    <textarea class='form-control' id='ticketbody' style='height: 10rem;' name='ticketbody' placeholder='Enter Concern here'><?php echo $results->content ?></textarea>
                </div>
                <div class='col-12'>
                    <button type='submit' id='editticks' name='editticks' style='margin-top: 2%;' class='btn btn-success'>Update</button>
                </div>
                <input type='hidden' name='ticketid' id='ticketid' value='<?php echo $tickid; ?>'/>
              </form>
          </div>
<?php
    }elseif(isset($_GET['viewticket'])){ //view ticket page
        $date = date('Y-m-d H:i:s');
        $ticketnumber = $_GET['viewticket'];
        //run query for update notification bubble
        $wpdb->query("UPDATE $exchange_tbl_name SET status='0' WHERE ticketid='$ticketnumber' AND date_sent < '$date'");
        $result2 = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$ticketnumber'");  
        $result3 = $wpdb->get_results("SELECT * FROM $exchange_tbl_name WHERE ticketid='$ticketnumber' ORDER BY date_sent DESC");
        $subject = $result2->subject;
        $base_url = get_site_url();
        $queue = $result2->queue;
?>
              <div class="container-fluid">
                <div class="row">
                  <div class="col"><h2 style="margin-top: 2%; margin-left: 2%;">Ticket Details</h2></div>
                  <div class="col">
                      <a class="btn btn-primary" style="float: right; margin-top: 2%; margin-right: 3%" 
                      href="admin.php?page=ticket_queue_tab"
                      >
                      Return to tickets list
                      </a>
                  </div>
                </div>
                <div class="my-3 p-3 bg-body rounded shadow-sm" style="margin-right: 2%;">
                      <div class="d-flex justify-content-between">
                          <h5 class="border-bottom">Subject: <?php echo $subject ?></h5>
                          <h5 class="border-bottom"><?php echo $result2->date_added ?></h5>
                      </div>
                      <div class="d-flex justify-content-between">
                          <ul class="list-group list-group-horizontal">
                              <li class="list-group-item">Task Type: <?php echo $result2->task_type ?></li>
                              <li class="list-group-item">Queue: #<?php echo $result2->queue ?></li>
                              <li class="list-group-item">Status: <?php echo $result2->status ?></li>
                          </ul>
                      </div>
                      <div class="col-8 border-bottom pb-1 pt-2 mb-0">
                          <h5>Content:</h5>
                          <h6><?php echo nl2br($result2->content) ?></h5>
                      </div>
  <?php 
                        if($result2->status!="Closed"){ 
  ?>
                            <div class="border-bottom pb-1 pt-2 mb-0">
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-10"><textarea class="form-control" id="replybox" name="replybox" placeholder="Enter reply here"></textarea></div>
                                        <div class="col-2"><button type="submit" id="replybtn" name="replybtn" class="btn btn-primary" style="margin-top: 3%;">Add Comment</button></div>
                                    </div>
                                    <input type='hidden' name='ticketid' id='ticketid' value='<?php echo $ticketnumber; ?>'/>
                                </form>
                            </div>
  <?php }
                          if(!empty($result3)){
                            foreach($result3 as $exchange){
                                    $content = nl2br($exchange->content);
                                    echo "
                                    <div class='d-flex text-muted pt-3'>
                                        <div class='pb-2 mb-0 medium lh-md border-bottom w-100'>
                                            <div class='d-flex justify-content-between'>
                                                <strong class='text-gray-dark'>$exchange->sender</strong>
                                                <strong class='text-gray-dark'>$exchange->date_sent</strong>
                                            </div>";
                                    if($exchange->sender!="Admin" && $result2->status!="Closed"){
                                    echo "
                                            <div class='d-flex justify-content-between'>
                                                <div class='col-10'><p class='pb-0 mb-0 w-100'>$content</p></div>
                                                <div class='col-1'><a href='admin.php?page=ticket_queue_tab&delexchange=$exchange->id' style='float:right;'>Delete</a></div>
                                            </div>";
                                    }else{
                                    echo "
                                            <div class='col-10'><p class='pb-0 mb-0'>$content</p></div>";
                                    }
                                    echo "
                                        </div>
                                    </div>";
                                    
                            }//foreach
                          }//if not empty
                      ?>
                </div>
              </div><!-- container fluid -->
    <?php
    }
  //FUNCTIONS FOR SUBMISSION
  if(isset($_POST['newticks'])){
      if(empty($_POST['ticketsubject']) || empty($_POST['ticketbody']) || empty($_POST['tasktype'])){
        echo "
        <script>alert('Fields are required!');</script>
        <script>location.replace('admin.php?page=ticket_queue_tab&newticket');</script>
        ";
      }else{//save new ticket entry and send to laravel
        $base_url = get_site_url();
        $date = date('Y-m-d H:i:s');
        $subject = $_POST['ticketsubject'];
        $content = $_POST['ticketbody'];
        $task_type = $_POST['tasktype'];
        $status = "Pending";
        $wpdb->query("INSERT INTO $table_name(queue, subject, task_type, content, status, last_modified, date_added)
        VALUES(0,'$subject','$task_type','$content','$status','$date','$date')");
        $data = $wpdb->get_row("SELECT * FROM $table_name ORDER BY ID DESC LIMIT 1");
        $license_type = $wpdb->get_var("SELECT license_type FROM $license_tbl WHERE id = 1"); //get license type
        //set api request config to send to laravel api
        $post_data = [
            'ticket_id' =>      $data->id,
            'base_url' =>       $base_url,
            'subject' =>        $data->subject,
            'task_type' =>      $data->task_type,
            'content' =>        $data->content,
            'status' =>         $data->status,
            'license_type' =>   $license_type,
            'last_modified' =>  $data->last_modified,
            'date_added' =>     $data->date_added,
        ];
        $data_push_to_api = json_encode($post_data);
        $url = 'https://dashboard.sg-webdesign.net/sendnewticket'; //set api url
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
        $queue = wp_remote_retrieve_body($response);
        //run query to update queue sent from laravel
        $wpdb->query("UPDATE $table_name SET queue='$queue' WHERE id='$data->id'");
        //save to database

        //var_dump($response);
        echo "
          <script>alert('Ticket Submitted!');</script>
          <script>location.replace('admin.php?page=ticket_queue_tab');</script>
          ";
      }
  }//newticks

  if(isset($_POST['editticks'])){
      $ticketid = $_POST['ticketid'];
      if(empty($_POST['ticketsubject']) || empty($_POST['ticketbody']) || empty($_POST['tasktype'])){
        echo "
        <script>alert('Fields are required!');</script>
        <script>location.replace('admin.php?page=ticket_queue_tab&editticket=$ticketid');</script>
        ";
      }else{//save new ticket entry and send to laravel
        $date = date('Y-m-d H:i:s');
        $base_url = get_site_url();
        $subject = $_POST['ticketsubject'];
        $content = $_POST['ticketbody'];
        $task_type = $_POST['tasktype'];
        $wpdb->query("UPDATE $table_name SET subject='$subject', task_type='$task_type', content='$content', last_modified='$date' 
                      WHERE id='$ticketid'");
        $data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$ticketid'");
        //set api request config to send to laravel api
        $post_data = array(
          'base_url' =>       $base_url,
          'subject' =>        $data->subject,
          'task_type' =>      $data->task_type,
          'content' =>        $data->content,
          'last_modified' =>  $data->last_modified,
        );
        $data_push_to_api = json_encode($post_data);
        $url = 'https://dashboard.sg-webdesign.net/ticketupdate/'.$ticketid; //set api url
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
        
        echo "
          <script>alert('Ticket Updated!');</script>
          <script>location.replace('admin.php?page=ticket_queue_tab');</script>
          ";
      }
  }//editticks

  if(isset($_POST['replybtn'])){
    $ticketid = $_POST['ticketid'];
        if(empty($_POST['replybox'])){
          echo "
          <script>alert('Fields are required!');</script>
          <script>location.replace('admin.php?page=ticket_queue_tab&viewticket=$ticketid');</script>
          ";
        }else{//save new ticket exchange entry and send to laravel
          $date = date('Y-m-d H:i:s');
          $base_url = get_site_url();
          $content = $_POST['replybox'];
          $status = 0;
          $current_user = wp_get_current_user();
          $sender = $current_user->user_email;
          $wpdb->query("INSERT INTO $exchange_tbl_name(ticketid, content, sender, status, date_sent)
                        VALUES('$ticketid', '$content', '$sender', '$status', '$date');");
          //run query for update ticket last modified
          $wpdb->query("UPDATE $table_name SET last_modified='$date' WHERE id='$ticketid'");
          //set api request config to send to laravel api
          $post_data = array(
            "base_url" =>       $base_url,
            "content" =>        $content,
            "sender" =>         $sender,
            "date_sent" =>      $date,
          );
          $data_push_to_api = json_encode($post_data);
          $url = 'https://dashboard.sg-webdesign.net/ticketclientreply/'.$ticketid; //set api url
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
          
          echo "
            <script>location.replace('admin.php?page=ticket_queue_tab&viewticket=$ticketid');</script>
            ";
        }
  }//replybtn

  if(isset($_GET['delexchange'])){
      $id = $_GET['delexchange']; //get ID
      $date = date('Y-m-d H:i:s'); //date
      $exchange = $wpdb->get_row("SELECT * FROM $exchange_tbl_name WHERE id='$id'"); //get comment data
      $base_url = get_site_url();
      $ticket_id = $exchange->ticketid;
      $content =   $exchange->content;
      $sender =    $exchange->sender;
      $date_sent = $exchange->date_sent;
      $wpdb->query("UPDATE $table_name SET last_modified='$date' WHERE id='$ticket_id'"); //update last modified
      //set api request config to send to laravel api
      $post_data = array(
        "base_url" =>       $base_url,
        "content" =>        $content,
        "sender" =>         $sender,
        "date_sent" =>      $date_sent,
      );
      $data_push_to_api = json_encode($post_data);
      $url = 'https://dashboard.sg-webdesign.net/ticketreplydel/'.$ticket_id; //set api url
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
      $wpdb->query("DELETE FROM $exchange_tbl_name WHERE id='$id'");
      echo "
        <script>location.replace('admin.php?page=ticket_queue_tab&viewticket=$ticket_id');</script>
        ";

  }//delexchange -- delete comment from wordpress and laravel

} // function


//add ajax function to button check click counter to initialize ajax function upon calls
add_action( 'wp_ajax_refreshqueue', 'refreshqueue');
add_action( 'wp_ajax_nopriv_refreshqueue', 'refreshqueue' );
//add function to admi menu on every page
add_action( 'in_admin_footer', 'queueRefresh' );

function refreshqueue(){
  global $wpdb;
  $license_tbl = $wpdb->prefix . 'license_check';
  $get_license_type = $wpdb->get_var("SELECT license_type FROM $license_tbl WHERE id = 1"); //get license type
  $current_queue_url = 'https://dashboard.sg-webdesign.net/ticketcurrentqueue/'.$get_license_type; //set api url
  $currentqueue = wp_remote_get($current_queue_url); //execute API request
  $next_in_queue = intval(wp_remote_retrieve_body($currentqueue)); //get response from laravel
  echo $next_in_queue;
  die();
}

function queueRefresh() {
?>
  <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
  <script type="text/javascript" >
    setInterval(function(){ 
        jQuery(function ($) {
          $( document ).ready(function() {
              $.ajax({
                  url: ajaxurl,
                  data: {
                        'action' : 'refreshqueue',
                        },
                  success: function(res) {
                    var curqueue = parseInt(res);
                    var nextqueue = curqueue+1;
                    $("#nowserving").html("Now Serving: #" + curqueue);
                    $("#nextserving").html("Next in Queue: #" + nextqueue);
                  }
              });
          });
        });
      }, 2000);
  </script>
<?php
}
