<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 23/05/14
 * Time: 6:35 PM
 */

require_once 'obj/db.php';

$db = new db();

try {
    $stmnt = "select * from ladder";
    $x = $db->getData($stmnt);
    print_r($stmnt);
} catch(PDOException $ex) {
    echo $ex->getMessage();
}