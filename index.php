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
include 'index_functions.php';
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

?>


<head>
    <!-- styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/10_hero_challenge.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        $(window).load(function(){
            $('.loading').fadeOut('slow');
            $('.jumbotron').fadeIn('slow');
        })// end load
        
        $(function(){
            $('.reroll').click(function(){
                $('.loading').html("<h1> Rerolling your incomplete heroes! </h1>");

                //Hide content and show load screen
                $('.jumbotron').fadeOut('slow');
                $('.loading').fadeIn('slow');

                //Get steam id stored in hidden variable
                var steamID_64 = $('.steam_id').val(); 

                // Call index_function with ajax and pass params to call reroll heroes
                $.get('index_functions.php', {action: 'reroll', steam_id: steamID_64}, update_heroes);
                return false;
            });//end click

            function update_heroes(data){
                //Refresh the page
                location.reload(true);
            }
        }); // end document ready

        //Top buttons decide displayed content
        $(document).ready(function(){
            $("#ladder").hide(); // hide ladder when document ready

            $("#profile_anchor").click(function(){
                $("#profile").show();
                $("#ladder").hide();
            });

            $("#ladder_anchor").click(function(){
                $("#profile").hide();
                $("#ladder").show();
            });
        });

    </script>
    <!-- Just a note  that < IE8 don't support font-face -->
    <style>
        @font-face
        {
            font-family: dota10Font;
            src: url(fonts/glyphicons-halflings-regular.woff);
        }

        div
        {
            font-family:dota10Font;
        }
    </style>
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
                    echo '<li><a id="profile_anchor" href="#">Profile</a></li>';
                } else {
                    echo '<li><a href="?login">Profile</a></li>';
                }?>
                <li><a id="ladder_anchor" href="#">Leaderboard</a></li>
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
        <div id="profile">
            <?php
            if (isset($user)) {
                //get info from ladder for user
                    $ladder_stmt = $mysqli->prepare("select * from ladder where steam_id = ?");
                    $ladder_stmt->bind_param("s",$user['response']['players'][0]['steamid']);
                    $ladder_stmt->execute();
                    $ladder_results = $ladder_stmt->fetch();

                echo "<div class=\"row\">";
                    echo "<div class=\"userName\"><h3>{$user['response']['players'][0]['personaname']}</h3></div>";
                    echo "<div class=\"span1 offset10\"><h4>Points: {$ladder_results['points']}</h4></div>";
                    echo "<div class=\"span5\"><img src=\"{$user['response']['players'][0]['avatarfull']}\" class=\"img-polaroid\"></div>";
                    echo '<div class="span12"><hr></div>';
                    echo '<div class="span12 heroes_div"><h5>Your 10 heroes</h5>';
                    generate_current_hero_table($_SESSION['SteamID64']);
                    echo'</div>';
                    echo '<div class="span12">';
                    make_reroll_button($_SESSION['SteamID64']);
                    echo '</div>';

                    echo "<div class=\"span12\"><h5>History</h5></div>
                    <div class=\"span12\">
                        <table class=\"table\">
                            <tr>
                                <th>Set #</th>
                                <th>Heroes</th>
                                <th>Completed On</th>
                            </tr>";
                    generate_history_table($mysqli, $_SESSION['SteamID64']);
                    echo "</table>";
                    echo "</div></div>";




            } else {
                echo  '<h1 style="text-align:center;">DOTA 10 Hero Challenge</h1><p style="text-align:center;">Please log in using Steam</p>';
            }
            ?>
        </div>
        <div id="ladder">
            <?php
            get_ladder();
            ?>
        </div>
    </div>
</div>
</body>
