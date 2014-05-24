<?php

/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 21/05/14
 * Time: 12:33 AM
 */

class db
{
    protected $db;

    public function __construct()
    {
        $this->$db = new PDO('mysql:host=localhost;dbname=dotakeeg_admin;charset=utf8', 'dotakeeg_admin', 'dota10');
    }

    function getData($db) {
        $stmt = $this->db->query($db);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
