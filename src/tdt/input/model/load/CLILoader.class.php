<?php

class CLILoader extends ALoader{
    
    public function execute(&$chunk){
        var_dump($chunk);
        
        echo "\n";
    }

}
