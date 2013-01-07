<?php

class RDFLoader extends ALoader{
    
    public function execute(&$chunk){
        $gs = new EasyRdf_GraphStore('http://localhost:2222/update/');
        $gs->insert($chunk);
    }

}
