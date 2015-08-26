<?php
add_action('admin_menu', 'votesys_setup_menu');

function votesys_setup_menu(){
        add_menu_page( 'Vote System plugin', 'Vote system', 'manage_options', 'vote_system_plugin', 'votesys_main_manu_init' );
        add_submenu_page('vote_system_plugin', 'Valdyti svetaines', 'Valdyti svetaines', 'manage_options', 'manage_sites', 'votesys_manage_form');
        add_submenu_page('vote_system_plugin', 'Prideti balsavimo svetaine', 'Prideti balsavimo svetaine', 'manage_options', 'add_vote_site', 'votesys_add_vote_site_form');
        add_submenu_page('vote_system_plugin', 'Redaguoti balsavimo svetaine', 'Redaguoti balsavimo svetaine', 'manage_options', 'edit_vote_site', 'votesys_edit_vote_site_form');
        
}

//optionu tvarkimo langas
function votesys_main_manu_init(){
    
    if(!isset($_POST['save_edit']))
    {
        generate_option_form();
    }
    else
    {
        $settings = get_option('votesys_options');
    
        $settings['add_point_sk'] = $_POST['add_point_sk'];
        $settings['track_by_ip'] = $_POST['track_by_ip'];
        $settings['track_by_time'] = $_POST['track_by_time'];
        $settings['advance_tracking'] = $_POST['advance_tracking'];
        $settings['help_href'] = $_POST['help_href'];

    
        update_option('votesys_options',$settings);
        
        generate_option_form();
    }
    
}

//pagrindinis balsavimo svetainiu valdimo langas
function votesys_manage_form()
{
    echo "<h1>Visos uzregistruotos svetaines</h1>";
    
    generate_site_table();
    
    echo "Prideti balsavimo svetaine: ";
    echo "<form action='".get_admin_url()."admin.php?page=add_vote_site' id='option_form' method='POST'>";
    echo "<input type='submit' value='PRIDETI'></form>";
        
}

//prideda balsavimo svetaine
function votesys_add_vote_site_form(){
    
    if(isset($_POST['save_site']))
    {
        save_site();
        echo "Sekmingai prideta svetaine: <b>".$_POST['site_name']."<br><br>";
        echo "<form action='".get_admin_url()."admin.php?page=add_vote_site' id='option_form' method='POST'>";
        echo "<input type='submit' value='PRIDETI DAR VIENA'></form>";
        
        echo "<form action='".get_admin_url()."admin.php?page=manage_sites' id='option_form' method='POST'>";
        echo "<input type='submit' value='GRYZTI I SVETAINIU VALDYMA'></form>";
    }
    else
    {
        generate_add_form();
    }
}

//redaguoja balsavimo svetaine
function votesys_edit_vote_site_form(){
    
    echo "<h1>Balsavimo svetaines redagavimas</h1>";
    
    if(isset($_POST['save_edit']) && isset($_POST['site_id']))
    {
        edit_site();
        echo "Balsavimo svetaine <b>".$_POST['site_name']."</b> sekmingai atnaujinta!<br><br>";
        echo "<form action='".get_admin_url()."admin.php?page=edit_vote_site' id='option_form' method='POST'>";
        echo "<input type='hidden' name='site_id' value='".$_POST['site_id']."'>";
        echo "<input type='submit' value='GRYZTI I REDAGAVIMA'></form>";
        
        echo "<form action='".get_admin_url()."admin.php?page=manage_sites' id='option_form' method='POST'>";
        echo "<input type='submit' value='GRYZTI I SVETAINIU VALDYMA'></form>";
    }
    else if(isset($_POST['site_id'])&& isset ($_POST['delete_site']))
    {
        delete_site();
        echo "<b>Svetaine sekmingai istrinta!</b>";
        echo "<form action='".get_admin_url()."admin.php?page=manage_sites' id='option_form' method='POST'>";
        echo "<input type='submit' value='GRYZTI I SVETAINIU VALDYMA'></form>";
    }
    else if (isset ($_POST['site_id']))
        generate_edit_form();
    else
    {
        echo "<b>Prasome pasirinkti svetaine redagavimui!</b><br><br>";
        generate_site_table();        
    }
    
}

//PAGRINDINIO LANGO FORMOS GENERAVIMAS
function generate_option_form(){
    
    $settings = get_option('votesys_options');
    
    echo "<h1>Balsavimo systemos valdymas</h1>"; 

    echo "<form action='".get_admin_url()."admin.php?page=vote_system_plugin' id='option_form' method='POST'>";
    echo "<input type='hidden' name='save_edit' value='1'>";
    
    echo "Uz balsa duodamas tasku skaicius: ";
    echo "<input type='number' name='add_point_sk' value='".$settings['add_point_sk']."'><br><br>";
    
    echo "Sekti balsavima pagal IP adresa ?: ";
    echo "<select name='track_by_ip' form='option_form'>";
        echo "<option value='1'";
            if($settings['track_by_ip'] == 1)
                echo " selected>TAIP</option>";
            else
                echo ">TAIP</option>";
    
        echo "<option value='0'";
            if($settings['track_by_ip'] == 0)
                echo " selected>NE</option></select><br><br>";
            else
                echo ">NE</option></select><br><br>";
    
    echo "Sekti balsavima pagal laika ?: ";
    echo "<select name='track_by_time' form='option_form'>";
        echo "<option value='1'";
            if($settings['track_by_time'] == 1)
                echo " selected>TAIP</option>";
            else
                echo ">TAIP</option>";
    
        echo "<option value='0'";
            if($settings['track_by_time'] == 0)
                echo " selected>NE</option></select><br><br>";
            else
                echo ">NE</option></select><br><br>";
    
    echo "Sekti balsavima pagal balsu skaicius pries ir po balsavimo ?: ";
    echo "<select name='advance_tracking' form='option_form'>";
        echo "<option value='1'";
            if($settings['advance_tracking'] == 1)
                echo " selected>TAIP</option>";
            else
                echo ">TAIP</option>";
    
        echo "<option value='0'";
            if($settings['advance_tracking'] == 0)
                echo " selected>NE</option></select><br><br>";
            else
                echo ">NE</option></select><br><br>";        
    
    echo "Pagalbos nuoroda, kuri bus rodoma pagrindiniame balsavimo lange: ";
    echo "<input type='text' name='help_href' value='".$settings['help_href']."'><br><br>";
            
    echo "<input type='submit' value='ISSAUGOTI'></form>";
}

//PRIDEJIMO FORMOS GENERAVIMAS
function generate_add_form()
{
    echo "<h1>Prideti balsavimo svetaine</h1>";
    echo "<form action='".get_admin_url()."admin.php?page=add_vote_site' id='option_form' method='POST'>";
    echo "<input type='hidden' name='save_site' value='1'>";
    
    echo "Svetaines pavadinimas: <br>";
    echo "<input type='text' name='site_name'><br><br>";
    
    echo "Svetaines bannerio url: <br>";
    echo "<input type='text' name='site_banner_url'><br><br>";
    
    echo "Svetaines balsavimo url: <br>";
    echo "<input type='text' name='site_vote_url'><br><br>";
    
    echo "Svetaines balsavimo daznis (minutemis): <br>";
    echo "<input type='number' name='vote_interval'><br><br>";
    
    echo "Svetaines balsavimo daznio textas (vietoj 3600s ->1h): <br>";
    echo "<input type='text' name='vote_interval_text'><br><br>";
    
    echo "Svetaines tekstas kurio turi buti ieskoma ( pasitikrinti pagal kitus ): <br>";
    echo "<input type='text' name='look_up_text'><br><br>";
    
    echo "<input type='submit' value='PRIDETI'></form>";
}

//VISU REGISTRUOTU SVETAINIU LENTELES GENERAVIMAS
function generate_site_table()
{
    echo "<table>
            <tr>
                <th>ID</th>
                <th>PAVADINIMAS</th>
                <th>BALSAVIMO BANNERIO URL</th>
                <th>BALSAVIMO URL</th>
                <th>BALSAVIMO INTERVALAS (MINUTEMIS)</th>
                <th>BALSAVIMO INTERVALAS (TEKSTU)</th>
                <th>IESKOMA FRAZE</th>
                <th>REDAGUOTI</th>
                <th>TRINTI</th>
            </tr>";
    
    global $wpdb;        
    $table_name = $wpdb->prefix.'votesys_topsite';
    
    $results = $wpdb->get_results("SELECT * FROM  `".$table_name."`");
        
    foreach($results as $votesite)
    {
        echo "<tr>"
                ."<th>".$votesite->vote_site_id."</th>"
                ."<th>".$votesite->site_name."</th>"
                ."<th>".$votesite->site_banner_url."</th>"
                ."<th>".$votesite->site_vote_url."</th>"
                ."<th>".$votesite->vote_interval."</th>"
                ."<th>".$votesite->vote_interval_text."</th>"
                ."<th>".$votesite->lookup_text."</th>"
                ."<th><form action='".get_admin_url()."admin.php?page=edit_vote_site' id='option_form' method='POST'>"
                .    "<input type='hidden' name='site_id' value='".$votesite->vote_site_id."'>"
                .    "<input type='hidden' name='edit_site' value='1'>"
                .    "<input type='submit' value ='REDAGUOTI'></form></th>"
                ."<th><form action='".get_admin_url()."admin.php?page=edit_vote_site' id='option_form' method='POST'>"
                .    "<input type='hidden' name='site_id' value='".$votesite->vote_site_id."'>"
                .    "<input type='hidden' name='delete_site' value='1'>"
                .    "<input type='submit' value = 'TRINTI'></form></th>"
             ."</tr>";
    }
    echo "</table><br><br>";
}

function generate_edit_form(){
    
    $vote_site = get_votesite_by_id($_POST['site_id']);
    
    echo "<form action='".get_admin_url()."admin.php?page=edit_vote_site' id='option_form' method='POST'>";
    echo "<input type='hidden' name='save_edit' value='1'>";
    echo "<input type='hidden' name='site_id' value='".$_POST['site_id']."'>";
    
    echo "Svetaines pavadinimas: <br>";
    echo "<input type='text' name='site_name' value='".$vote_site->site_name."'><br><br>";
    
    echo "Svetaines bannerio url: <br>";
    echo "<input type='text' name='site_banner_url' value='".$vote_site->site_banner_url."'><br><br>";
    
    echo "Svetaines balsavimo url: <br>";
    echo "<input type='text' name='site_vote_url' value='".$vote_site->site_vote_url."'><br><br>";
    
    echo "Svetaines balsavimo daznis (minutemis): <br>";
    echo "<input type='number' name='vote_interval' value='".($vote_site->interval/60)."'><br><br>";
    
    echo "Svetaines balsavimo daznio textas (vietoj 60m ->1h): <br>";
    echo "<input type='text' name='vote_interval_text' value='".$vote_site->interval_text."'><br><br>";
    
    echo "Svetaines tekstas kurio turi buti ieskoma ( pasitikrinti pagal kitus ): <br>";
    echo '<input type="text" name="look_up_text" value="'.$vote_site->advanced_lookup_text.'"><br><br>';
    
    echo "<input type='submit' value='REDAGUOTI'></form>";
}

//NAUJOS SVETAINES ISSAUGOJIMAS
function save_site(){
    
     global $wpdb;
    
    $table_name = $wpdb->prefix.'votesys_topsite';
    
    $wpdb->get_var("INSERT INTO ".$table_name."(site_name, site_banner_url,site_vote_url, vote_interval,vote_interval_text,lookup_text)
                              VALUES ('".$_POST['site_name']."','".$_POST['site_banner_url']."','".$_POST['site_vote_url']."','".$_POST['vote_interval']."','".$_POST['vote_interval_text']."','".$_POST['look_up_text']."')");
}

//SVETAINES PASALINIMAS
function delete_site(){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix.'votesys_topsite';
    
    $wpdb->get_var("DELETE FROM `".$table_name."` WHERE `vote_site_id` = '".$_POST['site_id']."'");

}

//SVETAINES REDAGAVIMAS
function edit_site(){
    
    global $wpdb;
    
    $table_name = $wpdb->prefix.'votesys_topsite';
    
    $wpdb->get_var("UPDATE `".$table_name."` SET `site_name`='".$_POST['site_name']."',`site_banner_url`='".$_POST['site_banner_url']."',`site_vote_url`='".$_POST['site_vote_url']."',"
                        ."`vote_interval`='".$_POST['vote_interval']."', `vote_interval_text`='".$_POST['vote_interval_text']."', `lookup_text`='".$_POST['look_up_text']."' WHERE `vote_site_id`='".$_POST['site_id']."'");

}

