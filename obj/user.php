<?php
/**
 * User: Keegan Bailey
 * Date: 20/05/14
 * Time: 11:39
 *
 * Once DB is set up. user class will be created on user login. If they log in and do not have
 * a list of 10, it will be created for them, and if it is. It will just grab it from the DB?
 * more thought required
 *
 */

include 'hero.php';

class user {

    private $steamID;
    private $heroes = Array();
    private $completed;

    public function __construct($_steamID){
        $this->steamID = $_steamID;
        $this->setup_hero_list();
    }

    public function get_steamID(){
        return $this->steamID;
    }

    //return a array of hero objects
    public function get_hero_list(){
        return $this->heroes;
    }

    //sets up hero list. If first time, creates hero list
    private function setup_hero_list(){
        //TODO: Check DB for heroes list.

        if(count($this->heroes) < 1){
            $this->get_new_hero_list();
        }
    }

    private function get_new_hero_list(){
        //get 10 heroes and store objects in $this->heroes array
        $hero_ids[] = array_rand($this->get_hero_ids(), 100);

        foreach($hero_ids as $id){
            if(count($this->heroes) == 10){
                return;
            }

            $check = $this->check_list($id);
            if($check){
                $this->heroes[] += new hero($id);
            }
        }
    }

    private function get_hero_ids(){
        $json_heroes = file_get_contents('js/json/heroes.js');
        $json_decoded_heroes = (json_decode($json_heroes, true));
        $hero_id_array = array();
        foreach($json_decoded_heroes['result']['heroes'] as $hero){
            array_push($hero_id_array, $hero['id']);
        }

        return $hero_id_array;
    }

    private function check_list($_id){
        foreach($this->heroes as $hero){
            if($hero->get_id() == $_id){
                return false;
            }
        }

        return true;
    }
}