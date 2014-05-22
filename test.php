<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 21/05/14
 * Time: 3:26 PM
 */

include_once "obj/user_rev2.php";
include_once "obj/hero.php";

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

if(!isset($_POST['submit'])){
    echo "
    <form method='post' action=''>
        <input type='text' placeholder='SteamID or AccountID' pattern='[0-9]{17}|[0-9]{7}' required name = 'steamid' />
        <input type='submit' value='Submit' name='submit'/>
    </form>";
} else {
    $user = new user_rev2($_POST['steamid']);
    echo $user->get_steam_id_32();
    echo "<br>";
    echo $user->get_steam_id_64();
    echo "<br>";

    $json_decoded_heroes = json_decode(file_get_contents('js/json/heroes.json'), true);
    $hero_id_array = array();
    foreach ($json_decoded_heroes['result']['heroes'] as $hero) {
        array_push($hero_id_array, $hero['id']);
    }
    print_r($hero_id_array);

    $hero_ids = array_rand($hero_id_array, 10);
    $heroes = array();
    foreach ($hero_ids as $id) {
        $current_hero = new hero($id);
        array_push($heroes, $current_hero);
    }

    echo "<br>";
    print_r($heroes);
}