<head>
    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>
    <script src="js/bootstrap.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
</head>

<?php 

    include_once 'obj/user.php';
    include_once 'obj/hero.php';

    $steamID = $_GET['steam_id'];

    $current_user = new user($steamID);

    $hero_objects_array = Array();
    $current_hero_array = Array();

    $current_hero_array = $current_user->get_hero_list();
    echo $current_hero_array;
    foreach($current_hero_array as $hero_id){
        $hero = new hero($hero_id);
        array_push($hero_objects_array, $hero);
    }


?>
<body data-spy="scroll" data-target=".bs-docs-sidebar" style="padding:60px;">
<!-- Navbar ================================================== -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="./index.php"><img src="./img/logo.png" height="50" width="50"></a>
            <ul class="nav">
                <li class="active"><a href="?login">Profile</a></li>
                <li><a href="#">Leaderboard</a></li>
            </ul>

            <ul class="nav pull-right">
                <li>Logout</li>
            </ul>
        </div>
    </div>
</div>
<!-- End Navbar ============================================== -->
<hr>
<div class="jumbotron masthead" style="color: #000000;">
    <div class="container">
        <div class="row">
            <div class="span1"><h3>Personal_Name</h3></div>
            <div class="span1 offset10"><h4>Points: ########</h4></div>
            <div class="span5"><img src="img/140x140.gif" class="img-polaroid"></div>
            <div class="span12"><hr></div>
            <div class="span12"><h5>current_heroes</h5></div>
            <?php
            echo"<div class='row'>";
                for($i=0; $i<5; $i++){
                    $hero = $hero_objects_array[$i];
                    echo"<div class='span2 offset1'><img src='".$hero->get_image()."' class='img-polaroid'></div>";
                }
            echo"</div>";
            echo"<div class='row'>";
                for($i=5; $i<10; $i++){
                    $hero = $hero_objects_array[$i];
                    echo"<div class='span2 offset1'><img src='".$hero->get_image()."' class='img-polaroid'></div>";
                }
            echo"</div>";
            ?>
            <div class="span12"><h5>history</h5></div>
            <div class="span12">
                <table class="table">
                    <tr>
                        <th>Set #</th>
                        <th>Heroes</th>
                        <th>Completed On</th>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Axe.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Beastmaster.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Io.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Enigma.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Beastmaster.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Io.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Enigma.png" class="img-polaroid"></div>
                        </td>
                        <td>
                            2014-05-25
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Axe.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Beastmaster.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Io.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Enigma.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Beastmaster.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Io.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Enigma.png" class="img-polaroid"></div>
                        </td>
                        <td>
                            2014-05-24
                        </td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>
                            <div class="span1"><img src="img\heroes\Axe.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Beastmaster.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Io.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Enigma.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Beastmaster.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Doom.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Io.png" class="img-polaroid"></div>
                            <div class="span1"><img src="img\heroes\Enigma.png" class="img-polaroid"></div>
                        </td>
                        <td>
                            2014-05-21
                        </td>
                    </tr>
                </table>
            <div class="row">
                <div class="span1 offset1"><img src="img/heroes/Axe.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1">Completed</div>
            </div>
            <div class="row">
                <div class="span1 offset1"><img src="img/heroes/Axe.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1">Completed</div>
            </div>
            <div class="row">
                <div class="span1 offset1"><img src="img/heroes/Axe.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1">Completed</div>
            </div>
            <div class="row">
                <div class="span1 offset1"><img src="img/heroes/Axe.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1">Completed</div>
            </div>
            <div class="row">
                <div class="span1 offset1"><img src="img/heroes/Axe.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Beastmaster.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Doom.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Io.png" class="img-polaroid"></div>
                <div class="span1"><img src="img/heroes/Enigma.png" class="img-polaroid"></div>
                <div class="span1">Completed</div>
            </div>
        </div>
    </div>
</div>
</body>
