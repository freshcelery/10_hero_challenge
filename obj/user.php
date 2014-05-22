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

include_once 'hero.php';

class user {

    private $steamID;
    private $steamID_32;
    private $heroes = Array();
    private $completed;
    private $current_timestamp;

    public function __construct($_steamID, $_steamID_32){
        $this->steamID = $_steamID;
        $this->steamID_32 = $_steamID_32;
        //$this->get_new_hero_list();
    }

    public function get_steamID(){
        return $this->steamID;
    }

    public function get_steamID_32(){
        return $this->steamID_32;
    }

    //return a array of hero objects
    public function get_hero_list(){
        return $this->heroes;
    }


    //sets up hero list. If first time, creates hero list
    public function setup_hero_list(){
        //TODO: Check DB for heroes list.

        if(count($this->heroes) < 1){
            $this->get_new_hero_list();
        }
    }

    public function get_match_win(){
        $json_player = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&account_id='.$this->steamID);
        $json_decoded_player = json_decode($json_player, true);

        foreach($json_decoded_player['result']['matches'] as $matches){
            $match_id = $matches['match_id'];
            echo $match_id;
            $user_win = $this->did_user_win($match_id);

            if($user_win == true){
                echo " You won <br />";
            }
            else{
                echo" You lost <br />";
            }
        }
    }

    public function get_new_hero_list(){
        //get 10 heroes and store objects in $this->heroes array
        $hero_ids = array_rand($this->get_hero_ids(), 10);

        foreach($hero_ids as $id){

            $check = $this->check_list($id);
            if($check){
                $current_hero = new hero($id);
                array_push($this->heroes, $current_hero);
            }
        }
    }

    private function get_hero_ids(){
        $json_heroes = file_get_contents('https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&language=en_us');
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

    /*
    * Get current Unix timestamp 
    */
    private function get_date(){
        $date = new DateTime();
        $this->current_timestamp = $date->getTimestamp();
    }

    /*
    * Function to get the hero names of their most recent 100 dota2 matches
    *
    * $player_json - The parsable json for the last 25 matches of the user
    * $account_id_32 - the 32 bit account id of the user, used for finding which player the hero is in a game
    */
    private function get_player_info($player_json, $account_id_32){
        $json_heroes = file_get_contents('../js/json/heroes.json');
        $json_decoded_heroes = (json_decode($json_heroes, true));

        foreach($player_json->result->matches as $matches){
            foreach($matches->players as $players){
                if($players->account_id == $account_id_32){
                    $hero_id = $players->hero_id;
                }
            }
        }
    }

    private function get_match_id(){
        $json_player = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&account_id='.$this->steamID);
        $json_decoded_player = (json_decode($json_heroes, true));

        foreach($json_decoded_player['result']['matches'] as $matches){
            if($matches['timestamp'] > $this->current_timestamp){
                foreach($matches->players as $players){
                    if($players->account_id == $account_id_32){
                        continue;
                    }
                    else{
                        return;
                    }
                }
            }

            return $matches->match_id;

        }
    }

    private function did_user_win($match_id){

        $json_match = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&match_id='.$match_id);
        $json_decoded_match = json_decode($json_match, true);

        $json_player = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&account_id='.$this->steamID);
        $json_decoded_player = (json_decode($json_player, true));

        $radiant_win = $json_decoded_match['result']['radiant_win'];
        
        foreach($json_decoded_player['result']['matches'] as $matches){
            if($matches['match_id'] == $match_id){
                foreach($matches['players'] as $player){
                    if($player['account_id'] == $this->steamID_32){
                        $player_slot = $player['player_slot'];
                    }
                }
            }
        }

        $player_slot = $this->find_player_side($player_slot);

        //If the player was on the radiant side
        if($player_slot == 'radiant'){
            //If the radiant won, the player won
            if($radiant_win == true){
                return true;
            }
            //else the player lost
            else{
                return false;
            }
        }
        //If the player was on the dire side
        else{
            //If the radiant lost the player won
            if($radiant_win == false){
                return true;
            }
            //else the player lost
            else{
                return false;
            }
        }
    }


    private function find_player_side($player_slot){
        if($player_slot < 10){
            return 'radiant';
        }
        else{
            return 'dire';
        }
    }     

}