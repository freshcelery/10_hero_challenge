<?php
/**
 * User: Keegan Bailey
 * Date: 02/06/14
 * Time: 1:03 PM
 */
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

?>


<head>
    <!-- styles -->
    <link href="../css/bootstrap.css" rel="stylesheet">
    <link href="../css/10_hero_challenge.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="../js/bootstrap.js"></script>
</head>
<body data-spy="scroll" data-target=".bs-docs-sidebar" style="padding:40px;">

<!-- Navbar ================================================== -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="../index.php"><img src="../img/logo.png" height="50" width="50"></a>
            <ul class="nav">
                <li><a href="../index.php">Profile</a></li>';
                <li class="active"><a href="#">Leaderboard</a></li>
            </ul>

            <ul class="nav pull-right">
                <li>Logout</li>
            </ul>
        </div>
    </div>
</div>
<!-- End Navbar ============================================== -->
<hr>
<div class="jumbotron masthead" style="display: none;">
    <div class="container">
        <?php get_du_table(); ?>
    </div>
</div>
</body>
