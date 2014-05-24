<?php

/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 21/05/14
 * Time: 12:33 AM
 */

class db
{
    private $db;

    public function __construct()
    {
        $this->$db = new PDO('mysql:host=dota.keeganbailey.com;dbname=dotakeeg_admin;charset=utf8', 'dotakeeg_admin', 'dota10');
    }

    public function query($_q){
        try{
            return $this->db->query($_q);
        } catch (exception $e) {
            return $e->getMessage();
        }
    }
}
