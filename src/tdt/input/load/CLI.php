<?php

namespace tdt\input\load;

class CLI extends \tdt\input\ALoader{
    public function execute(&$chunk){
        if(method_exists($chunk, "to_ntriples")){
            var_dump($chunk->to_ntriples());
        }else{
            var_dump($chunk);
        }
        echo "\n";
    }

}
