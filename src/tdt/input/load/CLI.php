<?php

namespace tdt\input\load;

class CLI extends \tdt\input\ALoader{
    
    public function execute(&$chunk){    	
        var_dump($chunk);
        
        echo "\n";
    }

}
