<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 23/05/14
 * Time: 9:57 PM
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
        $login = "<a href='?login'><img src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_large_noborder.png'></a>";
        //  http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png
        //  http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_large_border.png
        //  http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_large_noborder.png
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
        header("Location: index.php");
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
    checkDBforFirstLogIn($user);
}

//function that checks DB for user for the first time. if no there, creates.
function checkDBforFirstLogIn($_user){
    //get db object in the most unsecure way ever
    $db = new PDO('mysql:host=localhost;dbname=dotakeeg_admin;charset=utf8', 'dotakeeg_admin', 'dota10');
    try{

        //save username and id to vars
        $id = $_user['response']['players'][0]['steamid'];
        $name = $_user['response']['players'][0]['personaname'];

        //check DB for user
        $stmt = $db->query("SELECT * FROM 'ladder' WHERE 'steam_id' = {$id}");
        //$results = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$stmt == null){
            return;
        } else {
            //if no user, insert new entry into DB
            //$db->query("INSERT INTO 'ladder' ('steam_id', 'points', 'name') VALUES ('$id', 0,'$name')");
            $sql = "INSERT INTO ladder (steam_id,points,name) VALUES (:steam_id,:points,:name)";
            $q = $db->prepare($sql);
            $q->execute(array(':steam_id'=>$id,
                ':points'=>0,
                ':name'=>$name));

        }
    } catch(PDOException $e) {
        echo $e->getMessage();
    }
}

?>
<head>
    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>

    <script src="js/bootstrap.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>

</head>
<body data-spy="scroll" data-target=".bs-docs-sidebar" style="padding:40px;">
<!-- Navbar ================================================== -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="./index.php"><img src="./img/logo.png" height="50" width="50"></a>
            <ul class="nav">
                <?php
                if (isset($user)) {
                    echo '<li class="active"><a href="#">Profile</a></li>';
                } else {
                    echo '<li><a href="?login">Profile</a></li>';
                }?>
                <li><a href="#">Leaderboard</a></li>
            </ul>

            <ul class="nav pull-right">
                <li><?php echo $login; ?></li>
            </ul>
        </div>
    </div>
</div>
<!-- End Navbar ============================================== -->
<hr>
<div class="jumbotron masthead">
    <div class="container">
        <?php
        if (isset($user)) {
            /* echo "<h1> {$user['response']['players'][0]['personaname']} </h1>";
            echo "</ br>";
            echo "<img src='" . $user['response']['players'][0]['avatarfull'] . "' alt='avatar'/>";
            echo "<form action='getHeroes.php' method='get'>
            <input type='hidden' name='steam_id' value='" . $SteamID64 . "'/>
            <input class='submit' type='submit' value='Get 10 Heroes'>
        </form>";
            echo "<div id='10_heroes'></div>"; */

            //get info from ladder for user
            $ladder_stmt = $db->query("SELECT * FROM 'ladder' WHERE 'steam_id' = {$user['response']['players'][0]['steamid']}");
            $ladder_results = $ladder_stmt->fetch(PDO::FETCH_ASSOC);

            echo "<div class=\"row\">";
                echo "<div class=\"span1\"><h3>{$user['response']['players'][0]['personaname']}</h3></div>";
                echo "<div class=\"span1 offset10\"><h4>Points: {$ladder_results['points']}</h4></div>";
                echo "<div class=\"span5\"><img src=\"{$user['response']['players'][0]['avatarfull']}\" class=\"img-polaroid\"></div>";
                echo '<div class="span12"><hr></div>';
                echo '<div class="span12"><h5>Your 10 /heroes</h5></div>';

                echo "<div class=\"span12\"><h5>History</h5></div>
                <div class=\"span12\">
                    <table class=\"table\">
                        <tr>
                            <th>Set #</th>
                            <th>Heroes</th>
                            <th>Completed On</th>
                        </tr>";
        } else {
            echo  '<h1 style="text-align:center;">DOTA 10 Hero Challenge</h1><p style="text-align:center;">Please log in using Steam</p>';
        }
        ?>
    </div>
</div>
</body>
