<?php

/**

 * Created by PhpStorm.

 * User: Keegan

 * Date: 23/05/14

 * Time: 6:35 PM

 */

$db = new PDO('mysql:host=localhost;dbname=dotakeeg_admin;charset=utf8', 'dotakeeg_admin', 'dota10');
   

try {
    $stmnt = "select * from ladder";
    $x = getData($stmnt, $db);
    foreach($x as $i){
	echo "{$i['steam_id']} = {$i['points']} <br>";
    }
} catch(PDOException $ex) {
    echo $ex->getMessage();
}


function getData($_in, $db) {
        $stmt = $db->query($_in);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}