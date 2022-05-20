<?php
global $wpdb;
$get_license_type = $wpdb->get_var("SELECT license_type FROM $license_tbl WHERE id = 1"); //get license type
$current_queue_url = 'https://dashboard.sg-webdesign.net/ticketcurrentqueue/'.$get_license_type; //set api url
$currentqueue = wp_remote_get($current_queue_url); //execute API request
$next_in_queue = intval(wp_remote_retrieve_body($currentqueue)); //get response from laravel
?>
    <ul class="list-group list-group-horizontal">
        <li class="list-group-item">Now Serving: #<?php echo $next_in_queue ?></li>
        <li class="list-group-item">Next in Queue: #<?php echo $next_in_queue+1 ?></li>
    </ul>
<?php
?>