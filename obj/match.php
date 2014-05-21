<?php
/**
 * User: Keegan Bailey
 * Date: 20/05/14
 * Time: 2:03 PM
 *
 * This class will contain information about matches. It will require the user class
 */

require 'user.php';

class match {

    private $matchID;
    private $userID;
    private $json_match_details;

    public function __construct($_matchID, user $_user){
        $this->matchID = $_matchID;
        $this->userID = $_user->get_steamID();
        $this->json_match_details = (json_decode(file_get_contents('GET STRING FOR MATCH'. $_matchID), true));
    }

    //TODO: the rest
}