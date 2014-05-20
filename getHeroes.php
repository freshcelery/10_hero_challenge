<?php
$steam_id = $_GET["steam_id"];
$player_account_id = convert_id($steam_id);
$json_player = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&account_id=76561198023854426');
$json_decoded_player = (json_decode($json_player, true));

function convert_id($id){
    if (strlen($id) === 17){
        $converted = substr($id, 3) - 61197960265728;
    }else{
        $converted = '765'.($id + 61197960265728);
    }
    return (string) $converted;
}

//function to get the hero names of their most recent 100 dota2 matches
function get_player_info($player_json, $account_id_32){
    $json_heroes = file_get_contents('https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&language=en_us');
    $json_decoded_heroes = (json_decode($json_heroes, true));

    foreach($player_json['result']['matches'] as $matches){
        foreach($matches['players'] as $players){
            if($players['account_id'] == $account_id_32){
                $hero_id = $players['hero_id'];
                $hero_name = get_hero($json_decoded_heroes, $hero_id);
                echo $hero_name."<br>";
            }
        }
    }
}

//function to get hero's name based off of ID
function get_hero($heroes,$hero_id){
    foreach($heroes['result']['heroes'] as $hero){
        if($hero['id'] == $hero_id){
            return $hero['localized_name'];
        }
    }
    return "could not find player name";
}

?>

<html>
    <head>
    </head>
    <body>
        <?php get_player_info($json_decoded_player, $player_account_id); ?>
    </body>
</html>
