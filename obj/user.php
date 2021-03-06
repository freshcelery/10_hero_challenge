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

    //Holds key value pair of match_id => hero_id 
    private $matches_after_timestamp = Array();

    //Holds all unique hero ids
    private $all_hero_ids = Array();

    //Current Unix Timestamp
    private $current_timestamp;

    //Current sequence number
    private $seq_num;

    //If the user has the option to reroll their uncompleted heroes (1 reroll per sequence)
    private $reroll_available;

    private $current_points;


    /*
    * Constructor function for creating a new user object
    *
    *$_steamID - 64 bit steam ID of the user
    *$_steamdID_32 - 32 bit steam ID of the user
    */
    public function __construct($_steamID){

        $this->steamID = $_steamID;
        $this->steamID_32 = $this->convert_id($_steamID);

        $this->get_seq_from_db();
        $this->insert_new_user();
        $this->get_reroll_from_db();
        $this->get_10_heroes_from_db();
        $this->get_match_id();
        $this->ten_hero_test();
    }

    /* START PUBLIC FUNCTIONS */

    // Returns the 64 bit steam ID of the user
    public function get_steamID(){
        return $this->steamID;
    }

    // Returns the 32 bit steam ID of the user
    public function get_steamID_32(){
        return $this->steamID_32;
    }

    // Return a sorted array of hero ids
    public function get_hero_list(){
        ksort($this->heroes);
        return $this->heroes;
    }

    //Return the user's current sequence number
    public function get_current_sequence(){
        return $this->seq_num;
    }

    // Returns a boolean value depicting if you can reroll your incomplete heroes
    public function get_reroll_available(){
        return $this->reroll_available;
    }

    public function get_current_points(){
        return $this->current_points;
    }

    /*
    * Delete the user's incomplete heroes and replace them with new heroes to play, can only run once per sequence
    *
    */
    public function reroll_incomplete_heroes(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        $this->get_10_heroes_from_db();

        //Create a temporary hero array to store the new heroes while we loop through
        $temp_hero_array = Array();

        //Set the all_hero_ids array if it is not already set
        $this->get_hero_ids();

        foreach($this->heroes as $hero => $completed){
            if($hero > 0){
                if($completed == 0){
                    //Get a new random hero ID

                    $random_hero = array_rand($this->all_hero_ids);
                    $new_hero_id = $this->all_hero_ids[$random_hero];
                    $temp_hero_array[$new_hero_id] = 0;
                }
                else{
                    $temp_hero_array[$hero] = 1;
                }
            }
        }

        //Set the heroes array to the newly created array
        $this->heroes = $temp_hero_array;

        //set reroll to 0 to indicate no reroll available
        $this->reroll_available = 0;

        //Update the database entry to show no reroll is available
        $update_reroll = "UPDATE hero SET reroll_available = 0 WHERE steam_id = ? AND seq_id = ?";
        if($query = $mysqli->prepare($update_reroll)){
            $query->bind_param("si",$this->steamID_32, $this->seq_num);
            $query->execute();
            $query->close();
        }

        $this->update_db_heroes();
    }


    /* END PUBLIC FUNCTIONS*/



    /* START USER CREATION FUNCTIONS */

        /*
    *Function used to convert the user's 64 bit ID to a 32 bit ID or vice versa
    *
    * $id - the user's 32 or 64 bit id to be changed
    */
    private function convert_id($id){
        if (strlen($id) === 17){
            $converted = substr($id, 3) - 61197960265728;
        }
        else{
            $converted = '765'.($id + 61197960265728);
        }
    return (string) $converted;
}


        /*
    * Checks the database if the current 32 bit steam ID exists in the DB, if not, create a new user with that ID
    * 
    * Calls $this->setup_hero_list() if the user doesn't already exist
    */
    private function insert_new_user(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        $select_from_db = "SELECT steam_id FROM hero WHERE steam_id = ? AND seq_id = ?";
        if($select_query = $mysqli->prepare($select_from_db)){
            $select_query->bind_param("si", $this->steamID_32, $this->seq_num);
            $select_query->execute();
            if($select_query->fetch()){
                $select_query->close();
            }
            else{

                if(isset($this->steamID_32)){
                    $statement = "INSERT IGNORE INTO hero (steam_id, seq_id, reroll_available) VALUES (?, ?, 1)";
                    if($query = $mysqli->prepare($statement)){
                        $query->bind_param("si", $this->steamID_32, $this->seq_num);
                        $query->execute();
                        $query->close();               
                    }
                }
                $this->setup_hero_list(); 
            }
        }
    }


    /*
    * Sets up hero list. If first time, creates hero list
    *
    */
    private function setup_hero_list(){
        //Check if there are no heroes uncompleted for the user, if so grab a new 10 hero set
        if(count($this->heroes) == 0){
        	$this->get_new_hero_list();
        }
    }

    /*
    * Get a new list of 10 heroes to be played, pushes the 10 heroes to the $heroes array
    *
    * Calls $this->update_db_heroes()
    */
    private function get_new_hero_list(){
        $this->get_hero_ids();
        //get 10 heroes and store hero_id in $this->heroes array
        $random_hero_array = array_rand($this->all_hero_ids, 10);

        //Set current_timestamp to the current time.
       	$this->set_timestamp();

        foreach($random_hero_array as $id){
            $value = $this->all_hero_ids[$id];
           	$this->heroes[$value] = 0;
        }

        $this->update_db_heroes();
    }


    /*
    * Get a list of all hero IDs for creating a new list of 10 heros
    *
    * Sets $this->all_hero_ids to an array of all unique hero IDS
    */
    private function get_hero_ids(){

        $json_heroes = file_get_contents('https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&language=en_us');
        $json_decoded_heroes = (json_decode($json_heroes, true));
        foreach($json_decoded_heroes['result']['heroes'] as $hero){
            array_push($this->all_hero_ids, $hero['id']);
        }
    }


    /*
    * Set current Unix timestamp on the Database and updates the current_timestamp variable to be the same
    *
    */
    private function set_timestamp(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        // Update the database to have the current timestamp
        $update_sql = "UPDATE hero SET create_timestamp=(SELECT UNIX_TIMESTAMP(NOW())) WHERE steam_id= ? AND seq_id = ?";
        if($q = $mysqli->prepare($update_sql)){   
            $q->bind_param("si", $this->steamID_32, $this->seq_num);
            $q->execute();
            $q->close();
        }


    	// Grab the current timestamp from the database
        $select_time = "SELECT create_timestamp FROM hero WHERE steam_id = ? AND seq_id = ?";
        if($query = $mysqli->prepare($select_time)){
            $query->bind_param("si",$this->steamID_32, $this->seq_num);
            $query->execute();
            $query->bind_result($this->current_timestamp);
            $query->fetch();
            $query->close();
            $this->current_timestamp = intval($this->current_timestamp);
        }
    }

    /*
    * Retrieves a list of all matches played by the user after the current_timestamp
    * 
    * Sets $this->matches_after_timestamp key to the match retrieved and the value to the hero ID played
    * Sets the current timestamp to now
    */
    private function get_match_id(){

        $json_player = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&account_id='.$this->steamID);

        $json_decoded_player = (json_decode($json_player, true));
        $hero = 0;
        if($json_decoded_player['result']['matches']){
            foreach($json_decoded_player['result']['matches'] as $matches){
                $int_timestamp = intval($matches['start_time']);
                if($int_timestamp > $this->current_timestamp){
                    foreach($matches['players'] as $players){
                        $match_id = $matches['match_id'];
                        if($players['account_id'] == $this->steamID_32){
                            $hero = $players['hero_id'];
                            $this->matches_after_timestamp[$match_id] = $hero;
                        }
                        else{
                            continue;
                        }
                    }

                    //Set timestamp to now to avoid re-checking previously checked matches
                    $this->set_timestamp();
                }
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
        $json_decoded_player = json_decode($json_player, true);



        foreach($json_decoded_player['result']['matches'] as $matches){
            foreach($matches['players'] as $player){
                if($player['account_id'] == $this->steamID_32){
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
        if(isset($this->matches_after_timestamp)){
            foreach ($this->matches_after_timestamp as $match => $hero_id){
                foreach($this->heroes as $hero => $value){
                    if($hero_id == $hero){
                        $win = $this->did_user_win($match);
                        if($win){
                            $this->heroes[$hero] = 1;
                        }
                    }
                }
            }
        }

        //Unsets $matches_after_timestamp on completion so we don't have to loop through already checked matches 
        unset($this->matches_after_timestamp);
        $this->update_db_heroes();
        $this->check_if_heroes_remain();
    }


    /*
    * Checks the heroes array to see if all of the heroes given to a user have been completed
    *
    */
    private function check_if_heroes_remain(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        $is_complete = 0;
    	foreach($this->heroes as $hero_id => $completed){
            if($hero_id > 0){
    		  //If the hero is completed, continue iteration
    		  if($completed == 1){
    			  $is_complete = 1;
    		  }
    		  //If the hero isn't completed end the function
    		  else{
    			$is_complete = 0;
                return;
    		  }
            }
    	}
        if($is_complete == 1){
    	   //If all heres are completed unset the heroes array
    	   unset($this->heroes);
            $this->seq_num++;
            $this->insert_new_user();
            $this->add_points_to_user();
        }
    }

    private function add_points_to_user(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        $this->get_user_points();
        $this->current_points += 100;

        $update_points_query = "UPDATE ladder SET points = ? WHERE steam_id = ?";
        if($query = $mysqli->prepare($update_points_query)){
            $query->bind_param("is",$this->current_points, $this->steamID_32);
            $query->execute();
            $query->close();
        } 
    }





    /* START GET DATABASE INFORMATION FUNCTIONS */

        /*
    * Get the current sequence number for this user from the database
    *
    * Sets the sequence number to 0 if there is no sequence number set
    */
    private function get_seq_from_db(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        $seq_num_array = null;
        $select_from_db = "SELECT seq_id FROM hero WHERE steam_id = ?";
        if($select_query = $mysqli->prepare($select_from_db)){
            $select_query->bind_param("s", $this->steamID_32);
            $select_query->execute();
            $select_query->bind_result($seq_num_array);
            if($select_query->fetch()){
                $select_query->close();
                if(is_array($seq_num_array)){
                    $this->seq_num = end($seq_num_array);
                }
                else{
                    $this->seq_num = $seq_num_array;
                }
            }
            else{
                $this->seq_num = 0;
            }
        }


    }


    /*
    * Update the database to contain the current 10 heroes, and whether they have been completed or not
    *
    */
    private function update_db_heroes(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');
        
        // Create 2 empty strings for handling the delimited input
        $completed_heroes = "";
        $uncompleted_heroes = "";
        if(count($this->heroes) > 0){
            //Fill the strings with the list of completed or uncompleted heroes, delimited by a comma
            foreach($this->heroes as $hero => $completed){
                if($completed == 1){
                    $completed_heroes .= $hero.",";
                }
                else{
                    $uncompleted_heroes .= $hero.",";
                }
            }

            $completed_heroes = rtrim($completed_heroes, ',');
            $uncompleted_heroes = rtrim($uncompleted_heroes, ',');
        }
        // Updates the database with the completed heroes of this 10 hero set
        $update_completed = "UPDATE hero SET complete_id_string = ? WHERE steam_id = ? AND seq_id = ?";
        if($query = $mysqli->prepare($update_completed)){
            $query->bind_param("ssi",$completed_heroes, $this->steamID_32, $this->seq_num);
            $query->execute();
            $query->close();
        }

        // Updates the database with the uncompleted heroes of this 10 hero set
        $update_uncompleted = "UPDATE hero SET hero_id_string = ? WHERE steam_id = ? AND seq_id = ?";
        if($query = $mysqli->prepare($update_uncompleted)){
            $query->bind_param("ssi",$uncompleted_heroes, $this->steamID_32, $this->seq_num);
            $query->execute();
            $query->close();
        }
    }

 /*
    * Grab the 10 heroes from the database and populate the $this->heroes Array 
    *
    */
    private function get_10_heroes_from_db(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        // Create empty variables
        $completed_heroes = '';
        $uncompleted_heroes = '';
        $completed_hero_array = Array();
        $uncompleted_hero_array = Array();

        // Grab the uncompleted heroes for this user
        $select_uncomplete_heroes = "SELECT hero_id_string FROM hero WHERE steam_id = ? AND seq_id = ?";
        if($uncomplete_query = $mysqli->prepare($select_uncomplete_heroes)){
            $uncomplete_query->bind_param("si",$this->steamID_32, $this->seq_num);
            $uncomplete_query->execute();
            $uncomplete_query->bind_result($uncompleted_heroes);
            $uncomplete_query->fetch();
            $uncomplete_query->close();
        }

        // Grab the completed heroes for this user
        $select_completed_heroes = "SELECT complete_id_string FROM hero WHERE steam_id = ? AND seq_id = ?";
        if($complete_query = $mysqli->prepare($select_completed_heroes)){
            $complete_query->bind_param("si",$this->steamID_32, $this->seq_num);
            $complete_query->execute();
            $complete_query->bind_result($completed_heroes);
            $complete_query->fetch();
            $complete_query->close();
        }
        if(isset($uncompleted_heroes)){
            unset($this->heroes);
            // Explode the strings by comma to get a list of the completed and uncompleted heroes
            if(isset($completed_heroes)){
               $completed_hero_array = explode(",",$completed_heroes,10);
            }
            $uncompleted_hero_array = explode(",",$uncompleted_heroes,10);

            // Update the heroes array to contain the correct heroes from DB
            if(count($completed_hero_array) != 0){
               foreach($completed_hero_array as $hero){
                  $this->heroes[$hero] = 1;
               }
            }
            if(count($uncompleted_hero_array) != 0){
               foreach($uncompleted_hero_array as $hero){
                  $this->heroes[$hero] = 0;
               }
            }
        }
    }

    /*
    * Query the database to find if the user still has a hero reroll available for this set
    *
    * Store boolean result in $this->reroll_available
    */
    private function get_reroll_from_db(){
        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        $select_reroll = "SELECT reroll_available FROM hero WHERE steam_id = ? AND seq_id = ?";
        if($complete_query = $mysqli->prepare($select_reroll)){
            $complete_query->bind_param("si",$this->steamID_32, $this->seq_num);
            $complete_query->execute();
            $complete_query->bind_result($this->reroll_available);
            $complete_query->fetch();
            $complete_query->close();
        }
    }

    private function get_user_points(){

        $mysqli = new mysqli('localhost','dotakeeg_admin','dota10','dotakeeg_admin');

        $select_reroll = "SELECT points FROM ladder WHERE steam_id = ?";
        if($complete_query = $mysqli->prepare($select_reroll)){
            $complete_query->bind_param("s",$this->steamID_32);
            $complete_query->execute();
            $complete_query->bind_result($this->current_points);
            $complete_query->fetch();
            $complete_query->close();
        }
    }

/* END GET DATABASE INFORMATION FUNCTIONS */

}//End user class

?>