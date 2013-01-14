<?php

namespace tdt\input\load;
/**
 * Prints the header information
 */
class HEADER extends \tdt\input\ALoader{
    
    public function execute(&$chunk){
        
        foreach($chunk as $k => $v){
            echo $k . " | ";
        }
        
        echo "\n";
        exit(0);
    }

}
