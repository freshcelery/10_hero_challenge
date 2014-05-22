<?php
/**
 * User: Keegan Bailey
 * Date: 20/05/14
 * Time: 2:03 PM
 *
 * This class will contain information about matches. It will require the user class
 */

require 'user.php';

class match
{

    private $apikey;
    private $matchID;
    private $userID;
    private $userID32;
    private $json_match_details;
    private $player_in_game;
    private $player_side;
    private $player_hero_id;
    private $winner;  //string
    private $player_win;  //bool
    private $start_time;

    public function __construct($_matchID, user_rev2 $_user, $_apikey)
    {
        $this->matchID = $_matchID;
        $this->apikey = $_apikey;
        $this->userID = $_user->get_steam_id_64();
        $this->userID32 = $_user->get_steam_id_32();
        $this->json_match_details = (json_decode(file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/?key=' . $this->$apikey . '&match_id=' . $_matchID), true));
        $this->check_match_for_player();
        $this->check_player_side();
        $this->check_hero_player_id();
        $this->check_winner();
        $this->did_player_win();
        $this->check_start_time();
    }

    public function get_start_time(){
        return $this->start_time;
    }

    public function get_player_win(){
        return $this->player_win;
    }

    public function get_user_id(){
        return $this->userID;
    }

    public function get_user_id32(){
        return $this->userID32;
    }

    public function get_winner(){
        return $this->winner;
    }

    public function get_player_hero_id(){
        return $this->player_hero_id;
    }

    public function get_player_side(){
        return $this->player_side;
    }

    public function get_player_in_game(){
        return $this->player_in_game;
    }

    public function get_match_id(){
        return $this->matchID;
    }

    public function check_start_time(){
        $this->start_time = $this->json_match_details['result']['start_time'];
    }

    public function check_match_for_player()
    {
        foreach ($this->json_match_details['result']['players'] as $players) {
            if ($players['account_id'] == $this->userID32) {
                $this->player_in_game = true;
                return;
            }
        }
        $this->player_in_game = false;
    }

    public function check_hero_player_id()
    {
        foreach ($this->json_match_details['result']['players'] as $players) {
            if ($players['account_id'] == $this->userID32) {
                $this->player_hero_id = $players['hero_id'];
                return;
            }
        }
    }

    public function check_player_side()
    {
        foreach ($this->json_match_details['result']['players'] as $players) {
            if ($players['account_id'] == $this->userID32) {
                if ($players['player_slot'] < 5) {
                    $this->player_side = "Radiant";
                } else {
                    $this->player_side = "Dire";
                }
                return;
            }
        }

    }

    public function check_winner()
    {
        $win = $this->json_match_details['result']['radiant_win'];

        if ($win) {
            $this->winner = "Radiant";
        } else {
            $this->winner = "Dire";
        }
    }

    public function did_player_win()
    {
        if ($this->winner == $this->player_side) {
            $this->player_win = true;
        } else {
            $this->player_win = false;
        }
    }
}