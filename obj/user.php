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
        if(count($this->heroes) < 1){
            $this->get_new_hero_list();
        }
    }

    private function get_new_hero_list(){
        //get 10 heroes and store objects in $this->heroes array
        for($i = 0; $i < 10; $i++){
            $hero_id = rand(1,110);
            $check = $this->check_list($hero_id);
            if($check){
                $this->heroes[] += new hero($hero_id);
                break;
            }
            $i--;
        }
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