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
include_once 'obj/user.php';
include_once 'obj/hero.php';

$user;
$OpenID = new LightOpenID("dota.keeganbailey.com");
$mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

if (!$OpenID->mode) {
    if (isset($_GET['login'])) {
        $OpenID->__set('identity', 'http://steamcommunity.com/openid');
        header("Location: {$OpenID->authUrl()}");
    }

    if (!isset($_SESSION['SteamAuth'])) {
        $login = "<a href='?login'><img src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_large_noborder.png'></a>";
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
    header("Location: index.php");
}

if (isset($_SESSION['SteamID64'])) {
    $SteamID64 = ltrim($_SESSION['SteamID64'], '/');
    $user = json_decode(file_get_contents("cache/$SteamID64.json"), true);
    //check_db_for_first_login($user, $mysqli);

}

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

function generate_history_table(mysqli $mysqli){
    $id = substr($_SESSION['SteamID64'], 3) - 61197960265728;

    $result = $mysqli->query("SELECT hero_id_string, seq_id, is_done FROM hero WHERE steam_id = $id ORDER BY seq_id DESC");

    if (!$result) {
        die($mysqli->error);
    }

    $list = Array();
    while ($row = mysqli_fetch_assoc($result)) {
        $seq_id = $row['seq_id'];
        $hero_id_string = $row['hero_id_string'];
        $is_done = $row['is_done'];

        //TODO: make or use a function to get all the images
        $hero_id_string = get_hero_image_list($hero_id_string);

        //This can be changed if we add a completed timestamp in, then I can have
        //time completed and in progress
        if($is_done){
            $is_done = "Completed";
        } else {
            $is_done = "In Progress";
        }
        $list[] = "<tr>
                     <td>{$seq_id}</td>
                     <td>{$hero_id_string}</td>
                     <td>{$is_done}</td>
                   </tr>";
    }
    return $list;
}

function generate_current_hero_table(){
    if(($_SESSION['SteamID64'] > '2147483647')){
        $SteamID_ = $_SESSION['SteamID64'];
    

        $current_user = new user($SteamID_);

        $current_heroes = $current_user->get_hero_list();
        if(isset($current_heroes)){
            foreach($current_heroes as $hero_id => $completed){
                $hero_obj = new hero($hero_id);
                if($completed == 'true'){
                    echo"<div class='span2'><img src='".$hero_obj->get_image()."' class='img-polaroid completed'></div>";
                }
                else{
                    echo"<div class='span2'><img src='".$hero_obj->get_image()."' class='img-polaroid'></div>";
                }
            }
        }
    }
}


?>
<head>
    <!-- styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/10_hero_challenge.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        $(window).load(function(){
            $('.loading').fadeOut('slow');
            $('.jumbotron').fadeIn('slow');
        })// end load
    </script>
</head>
<body data-spy="scroll" data-target=".bs-docs-sidebar" style="padding:40px;">
<div class="loading">
</div>
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
<div class="jumbotron masthead" style="display: none;">
    <div class="container">
        <?php
        if (isset($user)) {
            //get info from ladder for user
                $ladder_stmt = $mysqli->prepare("select * from ladder where steam_id = ?");
                $ladder_stmt->bind_param("s",$user['response']['players'][0]['steamid']);
                $ladder_stmt->execute();
                $ladder_results = $ladder_stmt->fetch();
            /*
            $select_stmt = $db->query("SELECT * FROM 'hero' WHERE 'steam_id' = {$user['response']['players'][0]['steamid']}");
            if(select_stmt == null){
                $select_results = heroTableEmpty();
            } else {
                $select_results = $select_stmt->fetch(PDO::FETCH_ASSOC);
            } */

            echo "<div class=\"row\">";
                echo "<div class=\"span1\"><h3>{$user['response']['players'][0]['personaname']}</h3></div>";
                echo "<div class=\"span1 offset10\"><h4>Points: {$ladder_results['points']}</h4></div>";
                echo "<div class=\"span5\"><img src=\"{$user['response']['players'][0]['avatarfull']}\" class=\"img-polaroid\"></div>";
                echo '<div class="span12"><hr></div>';
                echo '<div class="span12"><h5>Your 10 heroes</h5>';
                generate_current_hero_table();
                echo'</div>';

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
