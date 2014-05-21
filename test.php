<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 21/05/14
 * Time: 3:26 PM
 */

include_once "obj/user.php";


if(!isset($_POST['submit'])){
    echo "
    <form method='post' action=''>
        <input type='text' placeholder='SteamID or AccountID' pattern='[0-9]{17}|[0-9]{7}' required name = 'steamid' />
        <input type='submit' value='Submit' name='submit'/>
    </form>";
} else {
    if (strlen($_POST['steamid']) === 17){
        $steamID64 = $_POST['steamid'];
        $steamID32 = substr($_POST['steamid'], 3) - 61197960265728;
    } else {
        $steamID32 = $_POST['steamid'];
        $steamID64 = '765'.($_POST['steamid'] + 61197960265728);
    }

    $user = new user($steamID64, $steamID32);
    echo $user->get_steamID();
}