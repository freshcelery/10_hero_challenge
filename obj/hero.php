<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:53 AM
 *
 * This class will be used to store information about a specific hero.
 */

class hero {

    private $heroName;
    private $heroID;
    private $heroImage;
    private $json_heroes;

    public function __construct($_heroID) {
        $this->heroID = $_heroID;
        $this->json_heroes = file_get_contents('../js/json/heroes.js');
        $this->set_name();
        $this->set_image();
    }

    public function get_name(){
        return $this->heroName;
    }

    public function get_image(){
        return $this->heroImage;
    }

    public function get_id(){
        return $this->heroID;
    }

    public function set_name(){
        //file_get_contents('https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/?key=CD44403C3CEDB535EFCEFC7E64F487C6&language=en_us');
        $json_decoded_heroes = (json_decode($this->$json_heroes, true));

        foreach($json_decoded_heroes['result']['heroes'] as $hero){
            if($hero['id'] == $this->heroID){
                $this->heroName = $hero['localized_name'];
            }
        }
    }

    public function set_image(){
        $output  = str_replace(" ", "_", $this->heroName);
        $this->heroImage = "img/heroes/" . $output . ".png";
    }
}