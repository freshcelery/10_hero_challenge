<?php

/**

 * Created by PhpStorm.

 * User: Keegan

 * Date: 21/05/14

 * Time: 12:14 PM

 */
session_start();

include 'obj/openid.php';
include 'apikey.php';

$OpenID = new LightOpenID("dota.keeganbailey.com");

if (!$OpenID->mode) {
    if (isset($_GET['login'])) {
        $OpenID->__set('identity', 'http://steamcommunity.com/openid');
        header("Location: {$OpenID->authUrl()}");
    }

    if (!isset($_SESSION['SteamAuth'])) {
        $login = "<a href='?login'><img src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png'></a>";
    }
} elseif ($OpenID->mode == "cancel") {
    echo "Login Canceled";
} else {
    if (!isset($_SESSION['SteamAuth'])) {
        $_SESSION['SteamAuth'] = $OpenID->validate() ? $OpenID->__get('identity') : null;
        $_SESSION['SteamID64'] = str_replace("http://steamcommunity.com/openid/id", "", $_SESSION['SteamAuth']);

        if ($_SESSION['SteamAuth'] !== null) {
            $steam64 = str_replace("http://steamcommunity.com/openid/id", "", $_SESSION['SteamAuth']);
            $profile = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$apikey&steamids=$steam64");
            $buffer = fopen('cache/' . $steam64 . '.json', 'w+');
            fwrite($buffer, $profile);
            fclose($buffer);
        }
        header("Location: openidtest.php");
    }
}

if (isset($_SESSION['SteamAuth'])) {
    $login = '<a href="?logout">logout</a>';
}

if (isset($_GET['logout'])) {
    unset($_SESSION['SteamAuth']);
    unset($_SESSION['SteamID64']);
    header("Location: openidtest.php");
}

if (isset($_SESSION['SteamID64'])) {
    $SteamID64 = ltrim($_SESSION['SteamID64'], '/');
    $user = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $apikey . "&steamids=" . $SteamID64), true);
}

echo $login;



if (isset($user)) {

    echo "<h1> {$user['response']['players'][0]['personaname']} </h1>";

    echo "</ br>";

    echo "<img src='" . $user['response']['players'][0]['avatarfull'] . "' alt='avatar'/>";



    echo "<form action='getHeroes.php' method='get'>

            <input type='hidden' name='steam_id' value='" . $SteamID64 . "'/>

            <input class='submit' type='submit' value='Get 10 Heroes'>

        </form>";



    echo "<div id='10_heroes'></div>";

}

?>

</body>

</html>