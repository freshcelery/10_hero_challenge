<?php

/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 21/05/14
 * Time: 5:01 PM
 */
include_once 'hero.php';

class user_rev2
{
    private $steam_id_64;
    private $steam_id_32;
    private $user_file;
    private $heroes = Array();

    public function __construct($_id, $_file = null)
    {
        if ($_file != null) {
            $this->user_file = $_file;
        }

        if (strlen($_id) === 17) {
            $this->steam_id_64 = $_id;
            $this->steam_id_32 = substr($_id, 3) - 61197960265728;
        } else {
            $this->steam_id_32 = $_id;
            $this->steam_id_64 = '765' . ($_id + 61197960265728);
        }
    }

    //region Getters
    public function get_steam_id_64()
    {
        return $this->steam_id_64;
    }

    public function get_steam_id_32()
    {
        return $this->steam_id_32;
    }

    public function get_hero_list()
    {
        return $this->heroes;
    }
    //endregion

    public function get_new_hero_list()
    {
        $id_list = $this->get_hero_ids();
        $hero_ids = array_rand($id_list, 10);
        foreach ($hero_ids as $id) {
            $current_hero = new hero($id);
            array_push($this->heroes, $current_hero);
        }
    }

    private function get_hero_ids()
    {
        $json_decoded_heroes = json_decode(file_get_contents('js/json/heroes.json'), true);
        $hero_id_array = array();
        foreach ($json_decoded_heroes['result']['heroes'] as $hero) {
            array_push($hero_id_array, $hero['id']);
        }
        return $hero_id_array;
    }

}