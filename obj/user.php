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
    //holds key value pair of hero_id => completed (true or false)
    private $heroes = Array();
    private $current_timestamp;
    private $matches_after_timestamp = Array();


    /*
    * Constructor function for creating a new user object
    *
    *$_steamID - 64 bit steam ID of the user
    *$_steamdID_32 - 32 bit steam ID of the user
    */
    public function __construct($_steamID, $_steamID_32){

        $this->steamID = $_steamID;
        $this->steamID_32 = $_steamID_32;
        $this->get_match_id();
        $this->get_new_hero_list();
        $this->ten_hero_test();
        $this->print_hero_test();
    }


    // Returns the 64 bit steam ID of the user
    public function get_steamID(){
        return $this->steamID;
    }


    // Returns the 32 bit steam ID of the user
    public function get_steamID_32(){
        return $this->steamID_32;
    }



    // Return a array of hero objects
    public function get_hero_list(){
        return $this->heroes;
    }

    /*
    * Sets up hero list. If first time, creates hero list
    *
    */
    public function setup_hero_list(){

        //TODO: Check DB for heroes list.
        if(isset($this->heroes)){
            $this->get_new_hero_list();
        }
    }


    /*
    * Get a new list of 10 heroes to be played, pushes the 10 heroes to the $heroes array
    *
    */
    public function get_new_hero_list(){

        //get 10 heroes and store hero_id in $this->heroes array
        $hero_ids = array_rand($this->get_hero_ids(), 10);

        //Set current_timestamp to the current time.
        $this->get_timestamp();

        foreach($hero_ids as $id){
                $current_hero = new hero($id);
                $id = $current_hero->get_id();
                $this->heroes[$id] = 'false';
        }
    }


    /*
    * Get a list of all hero IDs for creating a new list of 10 heros
    * Returns an array containing all IDs
    */
    private function get_hero_ids(){

        $json_heroes = file_get_contents('https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&language=en_us');
        $json_decoded_heroes = (json_decode($json_heroes, true));
        $hero_id_array = array();
        foreach($json_decoded_heroes['result']['heroes'] as $hero){
            array_push($hero_id_array, $hero['id']);
        }
        return $hero_id_array;
    }


    /*
    * Get current Unix timestamp 
    */
    private function set_timestamp(){

        $date = new DateTime();
        $this->current_timestamp = $date->getTimestamp();

        // Update the database to have the current timestamp
        $sql = "UPDATE user SET timestamp=? WHERE steam_id=?";
        $q = $db->prepare($sql);
        $q->execute(array($this->current_timestamp,$this->steamID));
    }



    /*
    * Function to get the hero names of their most recent 25 dota2 matches
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


    /*
    * Retrieves a list of all matches played by the user after the current_timestamp
    * 
    * sets $matches_after_timestamp array to the matches retrieved, sets the current timestamp to now
    */
    private function get_match_id(){

        $json_player = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&account_id='.$this->steamID);

        $json_decoded_player = (json_decode($json_player, true));
        $hero = 0;
        foreach($json_decoded_player['result']['matches'] as $matches){
            if($matches['timestamp'] > $this->current_timestamp){
                foreach($matches['players'] as $players){
                    if($players['account_id'] == $this->steamID_32){
                        $hero = $players['hero_id'];
                    }
                    else{
                        continue;
                    }
                }
                $match_id = $matches['match_id'];
                $this->matches_after_timestamp[$match_id] = $hero;
                $this->set_timestamp();
            }
        }
    }


    /*
    * Check to see if the player won a specific match based off of the numerical match id
    *
    * Returns true if the player won, false if they lost
    */
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




    /*
    * Find which side (Radiant or Dire) the player is on
    *
    * returns the string "radiant" or "dire"
    */
    private function find_player_side($player_slot){

        if($player_slot < 10){
            return 'radiant';
        }
        else{
            return 'dire';
        }
    }     

    /*
    * Get the ID of the hero the user played in a specific match
    * 
    * $match_id - ID of the match we want to find the player's hero
    *
    * Returns the numerical hero ID
    */
    private function hero_played($match_id){

        $json_player = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&account_id='.$this->steamID);
        $json_decoded_player = (json_decode($json_player, true));



        foreach($json_decoded_player['result']['matches'] as $matches){
            foreach($matches['players'] as $player){
                if($player['account_id'] == $_steamID_32){
                    return $player['hero_id'];
                }
            }
        }
    }

    /*
    * Compares the user's 10 hero list to their own list of heros and set the hero to true if they have won a game with them
    *
    */
    private function ten_hero_test(){
        foreach ($this->matches_after_timestamp as $match => $hero_id){
            foreach($this->heroes as $hero => $value){
                if($hero_id == $hero){
                    $win = $this->did_user_win($match);
                    if($win){
                        $this->heroes[$hero] = 'true';
                    }
                }
            }
        }

        //Unsets $matches_after_timestamp on completion so we don't have to loop through already checked matches 
        unset($this->matches_after_timestamp);
    }

    // Testing function that prints the heroes array
    private function print_hero_test(){
        foreach($this->heroes as $match => $hero){
            echo $match." => ".$hero."<br />";
        }
    }


    /*
    * Checks the heroes array to see if all of the heroes given to a user have been completed
    *
    */
    private function check_if_heroes_remain(){
    	foreach($this->heroes as $hero_id => $completed){
    		//If the hero is completed, continue iteration
    		if($completed){
    			continue;
    		}
    		//If the hero isn't completed end the function
    		else{
    			return;
    		}
    	}

    	//If all heres are completed unset the heroes array
    	unset($this->heroes);
    }
}
