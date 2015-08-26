<?php

include 'VoteSites.php';
include 'VotingUser.php';
include 'LookupData.php';
include 'VoteTimer.php';


/* gauna viska is duomenu bazes	paiima vartotojo id, kiek tasku*/
function get_user_stats($user_id)
{
    
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_userdata';
    
    $query = $wpdb->get_row("SELECT * FROM `".$table_name."` WHERE `user_id` = '".$user_id."';");
    $voter = new VotingUser();

    if($query->num_rows >0)
    {
    	$voter -> id = $query->user_id;
        $voter -> points = $query->points;
    }
    else// jai nerado jokio info apie uzeri tiesiog sukuria tuscia ir pabando is naujo
    {
    	create_voter($user_id);
	
        $query = $wpdb->get_row("SELECT * FROM `".$table_name."` WHERE `user_id` = '".$user_id."';");
		
	$voter -> id = $query->user_id;
        $voter -> points = $query->points;
    }

    $table_name = $wpdb->prefix.'votesys_votetime';

    $query = $wpdb->get_results("SELECT * FROM `".$table_name."` WHERE `user_id` ='".$user_id."';");
	
    $vote_times = array();
	
    foreach($query as $result)
    {
        $vote_time = new VoteTimer();
        $vote_time->vote_site_id = $result->vote_site_id;
        $vote_time->vote_time = $result->vote_time;
        array_push($vote_times, $vote_time);
    }
    
    $voter -> vote_time = $vote_times;
    $voter -> ip = get_client_ip();
	
    return $voter;
}

//Suranda balsuotojo IP
function get_client_ip() {
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if($_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function vote($voter,$site_id)
{
    $settings = get_option('votesys_options');
    
    if($settings['track_by_time'])
    {
        if(can_vote($voter,$site_id))
        {
            //dadeda tasku
            add_points($voter,$settings['add_point_sk']);
		
            //panaikina senaji balsa mysqle
            delete_vote($voter -> id,$site_id);
		
            //uzregistruoja nauja balsa mysqle
            create_vote($voter -> id,$site_id);
        }
    }
    else
    {
        //dadeda tasku
        add_points($voter,$settings['add_point_sk']);
		
        //panaikina senaji balsa mysqle
        delete_vote($voter -> id,$site_id);
		
        //uzregistruoja nauja balsa mysqle
        create_vote($voter -> id,$site_id);
    }
}

function can_vote($voter,$site_id)
{
    $vote_timer = $voter -> get_vote_timer_by_site_id($site_id);
    
    if($vote_timer == null)
    {
	return true;
    }
    else
    {
	$time_past = time() - $vote_timer->vote_time;
	
	if($time_past >= get_votesite_by_id($site_id) -> interval)
	{
            return true;
	}
    }
    return false;
}

function lookup_pending($site_id,$voter)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_lookup';

    $query = $wpdb->get_results("SELECT * FROM `".$table_name."` WHERE `ip` = '".$voter->ip."' AND `site_id` = '".$site_id."' AND `added_points` IS NULL");
    
    return $query->num_rows > 0 ? true : false;
}

function lookup_points($site_id)
{
    $site = get_votesite_by_id($site_id);
	
    $web_content = file_get_contents($site -> site_vote_url);
    $match = array();
    
    preg_match($site-> advanced_lookup_text, $web_content, $match);
    
    $points = $match[1];
    return convert_to_int($points);
}

function add_lookup($site_id,$time,$voter)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_lookup';

    $points = lookup_points($site_id);
    $query = $wpdb->get_results("INSERT INTO `".$table_name."` (`points`,`site_id`,`time`,`ip`)  VALUES ('".$points."','".$site_id."','".$time."','".$voter->ip."');");
}

//kadangi nelabai yra galimybiu turet lookup id be dar 1 mysql uzklausos tiesiog pries sukuriant irasa mysqle reikia kintamajam pazimet laika ir ji naudot kaip id
 
function get_lookup_data($time)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_lookup';
    
    $query = $wpdb->get_results("SELECT * FROM `".$table_name."` WHERE `time` = '".$time."';");
	
    if($query->num_rows > 0)
    {
	foreach($query as $result)	
            $lookup_data = new LookupData($result->id,$result->points,$result->time,$result->added_points);
        
	return $lookup_data;
    }
}

function set_lookup_state($time,$state)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_lookup';
    
    $query = $wpdb->get_results("UPDATE `".$table_name."` SET `added_points` = '".$state."' WHERE `time` = '".$time."';");
}

function add_points($voter,$amount)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_userdata';
    
    $amount += $voter -> points;
    $id = $voter ->id;
    $query = $wpdb->get_results("UPDATE `".$table_name."` SET `points` = '".$amount."' WHERE `user_id` = '".$id."';");
}

function remove_points($voter,$amount)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_userdata';
    
    $amount = $voter -> points - $amount;
    $id = $voter ->id;
    
    $query = $wpdb->get_results("UPDATE `".$table_name."` SET `points` = '".$amount."' WHERE `user_id` = '".$id."';");
}

function delete_vote($voter_id,$site_id)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_votetime';
    
    $query = $wpdb->get_results("DELETE FROM `".$table_name."` WHERE `user_id` = '".$voter_id."' AND `vote_site_id` = '".$site_id."';");
}

function create_vote($voter_id,$site_id)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_votetime';
    
    $query = $wpdb->get_results("INSERT INTO `".$table_name."` (`user_id`,`vote_site_id`,`vote_time`) VALUES ('".$voter_id."','".$site_id."','".time()."');");
}

function create_voter($user_id)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_userdata';
    
    $wpdb->get_results("INSERT INTO `".$table_name."` (`user_id`, `points`) VALUES ('".$user_id."' ,'0');");
}

function get_votesite_by_id($id)
{    
    define( 'SHORTINIT', true );

    require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
    
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_topsite';

    $query = $wpdb->get_results("SELECT * FROM `wp_votesys_topsite` WHERE `vote_site_id` = '".$id."'");
    
    foreach ($query as $result) {
        $site = new VoteSites($result->vote_site_id, $result->site_name, $result->site_banner_url, $result->site_vote_url, $result->vote_interval, $result->vote_interval_text, $result->lookup_text);
    }
    
    return $site;
}

function convert_to_int($num)
{
    $value = 0;
    
    for($i = 0;strlen($num) > $i;$i++)
    {
        if(is_numeric($num[$i]))
        {
            $value *= 10;
            $value += intval($num[$i]);
        }
    }
    
    return $value;
}