<?php

include_once "obj/user.php";

//function that checks DB for user for the first time. if no there, creates.
function check_db_for_first_login($_user, mysqli $mysqli){
    try{
        //save username and id to vars
        $id = substr($_user['response']['players'][0]['steamid'], 3) - 61197960265728;
        $points = 0;
        $name = $_user['response']['players'][0]['personaname'];

        $result = $mysqli->query("SELECT * FROM ladder WHERE steam_id = $id");

        if (!$result) {
            die($mysqli->error);
        }

        if (! $result->num_rows > 0) {
            $result = $mysqli->query("INSERT IGNORE INTO ladder (steam_id,points,name) VALUES ('$id', '$points', '$name')");
        }

    } catch(mysqli_sql_exception $e) {
        echo $e->getMessage();
    }
}

function get_du_table(){
    $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');
    $result = $mysqli->query("SELECT * FROM ladder ORDER BY points DESC");

    if (!$result) {
        die($mysqli->error);
    }

    echo "<table class='table-striped'>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Points</th>
            </tr>";

    //for each row returned in query pull outi nfo needed, then create hero objects for every ID. make the table data and print it till out of rows.
    $i = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $points = $row['points'];
        $steam_id = '765'.($row['steam_id'] + 61197960265728);
        $name = $row['name'];

        echo "<tr>
                 <td>{$i}</td>
                 <td><a href='http://steamcommunity.com/id/{$steam_id}'>{$name}</a></td>
                 <td>{$points}</td>
              </tr>";

        $i++;
    }

    echo " </table>";
}

function generate_history_table(mysqli $mysqli, $steamdID_64){
    $id = substr($steamdID_64, 3) - 61197960265728;

    //get needed things from DB
    $result = $mysqli->query("SELECT hero_id_string, complete_id_string, create_timestamp, seq_id, is_done FROM hero WHERE steam_id = $id ORDER BY seq_id DESC");

    if (!$result) {
        die($mysqli->error);
    }

    //for each row returned in query pull outi nfo needed, then create hero objects for every ID. make the table data and print it till out of rows.
    while ($row = mysqli_fetch_assoc($result)) {
        $seq_id = $row['seq_id'];
        $hero_id_string = $row['hero_id_string'];
        $complete_id_string = $row['complete_id_string'];
        $is_done = $row['is_done'];
        $timestamp = date('F j, Y, g:i:a', $row['create_timestamp']);

        $hero_id_array = array_merge(explode(",", $hero_id_string), explode(",", $complete_id_string));

        $image_text = '';
        foreach($hero_id_array as $hero_id){
            if(!empty($hero_id)){
                $hero = new hero($hero_id);
                $image_text .= '<div class="history_div"><img src="'. $hero->get_image() .'" class="img-polaroid"></div>';
            }
        }

        //This can be changed if we add a completed timestamp in, then I can have
        //time completed and in progress
        if($is_done == 0){
            $is_done = "In Progress";
        } else {
            $is_done = $timestamp;
        }
        echo "<tr>
                 <td class='seq_id_td' >{$seq_id}</td>
                 <td class='hero_image_td' >{$image_text}</td>
                 <td class='is_done_td' >{$is_done}</td>
              </tr>";
    }
}

function generate_current_hero_table($steamdID_64){
    if(($steamdID_64 > '2147483647')){
        
        $current_user = new user($steamdID_64);

        $current_heroes = $current_user->get_hero_list();

        if(isset($current_heroes)){
            foreach($current_heroes as $hero_id => $completed){
                if($hero_id > 0){
                    $hero_obj = new hero($hero_id);
                    if($completed == 0){
                        echo"<div class='span2'><img src='".$hero_obj->get_image()."' class='img-polaroid'></div>";
                    }
                    else{
                        echo"<div class='span2'><img src='".$hero_obj->get_image()."' class='img-polaroid completed'></div>";
                    }
                }
            }
        }
    }
}

function make_reroll_button($steamdID_64){
    if(($steamdID_64 > '2147483647')){
        $current_user = new user($steamdID_64);

        $reroll = $current_user->get_reroll_available();
        if($reroll == 1){
            echo "<input type='button' class='reroll' value='Reroll Heroes' />";
            echo "<input type='hidden' class='steam_id' value='".$steamdID_64."' />";
        }
    }
}

function reroll_heroes($steamdID_64){
    if(($steamdID_64 > '2147483647')){
        $user = new user($steamdID_64);

        $user->reroll_incomplete_heroes(); 
        $hero_table = generate_current_hero_table($steamdID_64);
    }
}


if(isset($_GET['action']) && !empty($_GET['action'])) {
    if(isset($_GET['steam_id'])){
        $steamID = $_GET['steam_id'];
        if($steamID > '2147483647'){
            $action = $_GET['action'];
            switch($action) {
                case 'reroll';
                    reroll_heroes($steamID);
                    break;
                default;
                    break;
            }
        }
    }
}
?>