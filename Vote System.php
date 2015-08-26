<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
Plugin Name: Vote System
Plugin URI: http://mc.skilas.lt
Description: Balsavimo sistema leidzianti uz balsus duoti tasku
Version: 0.0.1
Author: Ideo
Author URI: http://skilas.lt
License: Private
Text Domain: Vote System
*/

register_activation_hook( __FILE__, 'votesys_install' );

//Activation
function votesys_install() {
   
    $votesys_default = array(
        'add_point_sk' => '1',
        'track_by_ip' => true,
        'track_by_time' => true,
        'advance_tracking' => true,
        'help_href' => 'http://mc.skilas.lt'
    );
    
    update_option('votesys_options',$votesys_default);
    
    global $wpdb;

    //add lookup table
    $table_name = $wpdb->prefix.'votesys_lookup';
	
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql =   "CREATE TABLE ".$table_name." (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `points` int(11) NOT NULL DEFAULT '0',
                    `site_id` int(11) NOT NULL DEFAULT '0',
                    `time` int(11) NOT NULL DEFAULT '0',
                    `added_points` tinyint(1) DEFAULT NULL,
                    `ip` varchar(200) NOT NULL DEFAULT '',
                    PRIMARY KEY (`id`)
                    )".$charset_collate.";";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    //add userdb table
    $table_name = $wpdb->prefix.'votesys_userdata';
    
    $sql =   "CREATE TABLE ".$table_name." (
                    `user_id` int(11) NOT NULL DEFAULT '0',
                    `points` int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`user_id`)
                    )".$charset_collate.";";
          
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    //add userdb table
    $table_name = $wpdb->prefix.'votesys_votetime';
    
    $sql =   "CREATE TABLE ".$table_name." (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL DEFAULT '0',
                    `vote_site_id` int(11) NOT NULL DEFAULT '0',
                    `vote_time` int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                    )".$charset_collate.";";
   
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    //add topsite table
    $table_name = $wpdb->prefix.'votesys_topsite';
    
    $sql =   "CREATE TABLE ".$table_name." (
                    `vote_site_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `site_name` varchar(200) NOT NULL DEFAULT '',
                    `site_banner_url` varchar(200) NOT NULL DEFAULT '',
                    `site_vote_url` varchar(200) NOT NULL DEFAULT '',
                    `vote_interval` int(11) NOT NULL DEFAULT '0',
                    `vote_interval_text` varchar(200) NOT NULL DEFAULT '',
                    `lookup_text` varchar(200) NOT NULL DEFAULT '',
                    PRIMARY KEY (`vote_site_id`)
                    )".$charset_collate.";";
   
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    register_uninstall_hook(__FILE__, 'votesys_uninstall');
}

//Diactivation
function votesys_uninstall(){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix."votesys_lookup";
    $wpdb->query("DROP TABLE IF EXISTS ".$table_name);
    
    $table_name = $wpdb->prefix."votesys_userdata";
    $wpdb->query("DROP TABLE IF EXISTS ".$table_name);
    
    $table_name = $wpdb->prefix."votesys_votetime";
    $wpdb->query("DROP TABLE IF EXISTS ".$table_name);
    
    $table_name = $wpdb->prefix."votesys_topsite";
    $wpdb->query("DROP TABLE IF EXISTS ".$table_name);
    
    delete_option('votesys_options');
}

include 'Functions.php';
include 'AdminPanel.php';

add_shortcode( 'votesys', 'votesys_gen_vote' );

function votesys_gen_vote(){
    
    if (is_user_logged_in()) 
    {
        
        $userdata = get_userdata(get_current_user_id());
                
	echo "<center>Sveikas ".$userdata->display_name."<br><br>";
	
	$vote_user = get_user_stats(get_current_user_id());
	$settings = get_option('votesys_options');
        
	echo "Jūs turite: ".$vote_user -> points." balsavimo taškus.<br><br>";
	echo "Visa informacija kodėl verta balsuoti, ką gausite balsuodami ir kaip tai padaryti nurodita <a href = '".$settings['help_href']."'> čia.</a> <br><br>";
	echo "<i>Pastaba: paslaugų uždėjimo sistemą kolkas yra tobulinama todėl jas kolkas galite atsiimti tik parašius administracijai skype arba pm (Ideo)</i><br><br>";
	echo"<b>Svetainės kuriose galite balsuoti</b><br><br>";
	
	echo"<table class ='vote-table-top' width = '100%'>";
	echo"<tr class ='vote-table-top'><th>Topo pavadinimas</th><th>Taškų kuriuos gausite skaičius</th><th>Galite balsuoti vieną karta per</th><th>Ar galite balsuoti?</th><th>Baneris</th></tr>";
	
        global $wpdb;        
        $table_name = $wpdb->prefix.'votesys_topsite';
        $results = $wpdb->get_results("SELECT * FROM  `".$table_name."`");
        
        
                
	foreach($results as $site)
	{
		echo"<tr class = 'vote-table-middle'><th class = 'vote-table-middle1'>".$site->site_name."</th>";
		echo"<th class = 'vote-table-middle1'>".$settings['add_point_sk']."</th>";
		echo"<th class = 'vote-table-middle1'>".$site->vote_interval_text."</th>";
		if(can_vote($vote_user,$site ->vote_site_id))
                    echo"<th style='color:green;'>TAIP</th>";
		else
                    echo"<th style='color:red;'>NE</th>";
				
		echo"<th class = 'vote-table-middle1'><a href=wp-content/plugins/Vote-system/vote.php?userid=".$vote_user->id."&siteid=".$site->vote_site_id."><img src =".$site->site_banner_url."></img></th></tr>";
	}
	
	echo "</table></center>";
	
    } 
    else 
    {
	echo "<center> Sveiki! <br><br> <div class = 'vote-warning' >Jus nesate prisijunge prie tinklapio todėl už atiduotus balsus negausite taškų !</div> <br>";
	
	echo "Visa informacija kodėl verta balsuoti, ką gausite balsuodami ir kaip tai padaryti nurodita <b>SICIA REIKIA HREFO PADORAUS NEZINAU KA RASYTTTTTTTTT</b><a href = ''> čia.</a> <br><br>";
	echo"<b>Svetainės kuriose galite balsuoti</b><br><br>";
	
	echo"<table class ='vote-table-top' width = '100%'>";
	echo"<tr class ='vote-table-top'><th>Topo pavadinimas</th><th>Taškų kuriuos gutumete balsuojant prisijungus skaičius</th><th>Baneris</th></tr>";
	
        global $wpdb;        
        $table_name = $wpdb->prefix.'votesys_topsite';
    
        $results = $wpdb->get_results("SELECT * FROM  `".$table_name."`");
        
        $settings = get_option('votesys_options');
        
	foreach($results as $site)
	{
		echo"<tr class = 'vote-table-middle'><th class = 'vote-table-middle1'>".$site->site_name."</th>";
		echo"<th class = 'vote-table-middle1'>".$settings['add_point_sk']."</th>";
		echo"<th class = 'vote-table-middle1'>".$site->site_web."</th></tr>";
	}
	
	echo "</table></center>";
	
    }
}