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

    private function __construct($_heroID) {
        $this->heroID = $_heroID;
        $this->set_name();
        $this->set_image();

    }

    public function set_name(){
        $json_heroes = file_get_contents('/js/heroes.js');
        $json_decoded_heroes = (json_decode($json_heroes, true));

        foreach($json_decoded_heroes['result']['heroes'] as $hero){
            if($hero['id'] == $this->heroID){
                $this->heroName = $hero['localized_name'];
            }
        }
    }

    public function set_image(){
        $output  = str_replace(" ", "_", $this->heroName);
        $this->heroImage = "/img/heroes/" . $output . ".png";
    }
}