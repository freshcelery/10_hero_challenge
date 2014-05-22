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
    private $hero_win;
    private $json_heroes;

    public function __construct($_heroID) {
        $this->heroID = $_heroID;
        $this->json_heroes = file_get_contents('/js/json/heroes.js');
        $this->hero_win = false;
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

    public function get_hero_win(){
        return $this->hero_win;
    }

    //manual setting hero win
    public function set_hero_win($_bool){
        if(!is_bool($_bool)) return;

        if($_bool){
            $this->hero_win = true;
        }
        $this->hero_win = false;
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