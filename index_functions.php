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

function generate_history_table(mysqli $mysqli, $steamdID_64){
    $id = substr($steamdID_64, 3) - 61197960265728;

    //get needed things from DB
    $result = $mysqli->query("SELECT hero_id_string, seq_id, is_done FROM hero WHERE steam_id = $id ORDER BY seq_id DESC");

    if (!$result) {
        die($mysqli->error);
    }

    //for each row returned in query pull outi nfo needed, then create hero objects for every ID. make the table data and print it till out of rows.
    while ($row = mysqli_fetch_assoc($result)) {
        $seq_id = $row['seq_id'];
        $hero_id_string = $row['hero_id_string'];
        $is_done = $row['is_done'];

        $hero_id_array = explode(",", $hero_id_string);

        $image_text = '';
        foreach($hero_id_array as $hero_id){
            if(!empty($hero_id)){
                $hero = new hero($hero_id);
                $image_text .= '<div class="span1"><img src="'. $hero->get_image() .'" class="img-polaroid"></div>';
            }
        }

        //This can be changed if we add a completed timestamp in, then I can have
        //time completed and in progress
        if($is_done){
            $is_done = "Completed";
        } else {
            $is_done = "In Progress";
        }
        echo "<tr>
                 <td>{$seq_id}</td>
                 <td>{$image_text}</td>
                 <td>{$is_done}</td>
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