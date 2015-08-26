<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

include 'Functions.php';

$user_id = $_GET['userid'];
$site_id = $_GET['siteid'];

$site = get_votesite_by_id($site_id);

$settings = get_option('votesys_options');
$vote_user = get_user_stats($user_id);

echo lookup_points($site_id);

/*
if($settings['advance_tracking'])
{
    if(can_vote($vote_user,$site_id) && !lookup_pending($site_id,$vote_user))
    {
	$lookup_time = time();
	
	add_lookup($site_id,$lookup_time,$vote_user);
	header('Location: '.$site->site_vote_url);
	
	flush();
	sleep(45);
	
	$lookup = get_lookup_data($lookup_time);
	$points = lookup_points($site_id);
		
	if($points > $lookup-> points_on_lookup)
	{
            vote($vote_user,$site_id);
            set_lookup_state($lookup_time,true);
	}
	else
            set_lookup_state($lookup_time,false);
    }
    else;
        header('Location: '.$site->site_vote_url);
}
else
{
	vote($vote_user,$site_id);
	header('Location: '.$site->site_vote_url);
        exit();
}*/