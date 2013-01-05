<?php

class TDTInput {

    //
    private $e,$m,$l;

    //$ts is an array of transformers, as more transformations can be 
    private $ts;
    
    public function __construct($config){
        //parse ini file for the extractor
        $this->
        //parse ini files for the transformers
        
        //parse ini file for the mapper
        
        //parse ini file for the loader
        
    }
    
    public function execute($source){
        Log::getInstance()->logInfo("Starting the extractor");
        while($this->e->hasNext()){

            $chunk = $this->e->pop();

            foreach($this->ts as $t){
                $chunk = $t->execute($chunk);
                Log::getInstance()->logInfo("Transform: ",$chunk);
            }
            
        }
    }
    

}
