<?php

/**
 * Input is the main model class
 * @author Pieter Colpaert
 * @author Miel Vander Sande
 */

namespace tdt\input;
use JsonSchema\Validator;
use RedBean_Facade as R;

class Input {

    //Extractor, Mapper, Loader
    private $e, $m, $l;

    public $log;

    private $db;
    
    public function __construct($config,$db = array()) {
        if(!empty($db)){
            $this->db = $db;
            R::setup($this->db["system"] . ":host=" . $this->db["host"] . ";dbname=" . $this->db["name"], $this->db["user"], $this->db["password"]);
        }

        $extractmethod = $config["extract"]["type"];
        $extract = $config["extract"];

        $extractorclass = "tdt\\input\\extract\\" . $extractmethod;
        $this->e = new $extractorclass($extract,$this->log);

        // mapper
        if(!empty($config["map"]) && !empty($config["map"]["type"])){
            $map = $config["map"];
            $mapmethod = "tdt\\input\\map\\" . $config["map"]["type"];
            $this->m = new $mapmethod($map, $this->log);
        }        
        
        // loader
        if(!empty($config["load"]) && !empty($config["load"]["type"])){
            $loadclass = "tdt\\input\\load\\" . $config["load"]["type"];            
            $this->l = new $loadclass($config["load"], $this->log);
        }        
    }
    

    /**
     * Execute our model according to the configuration parsed in the constructor
     */
    public function execute() {

        $start = microtime(true);
        $numberofchunks = 0;
        
        $this->log[] = "Started ETML process";
        $this->errors = array();
        
		    
        while ($this->e->hasNext()) {                
                //1. EXTRACT
                
		$chunk = $this->e->pop();
		
                //2. MAP
                if (!empty($this->m)) {
                    $chunk = $this->m->execute($chunk);
                }

                //3. LOAD
                if (!empty($this->l)) {
                    $this->l->execute($chunk);
                }
            
            // Either chunk is null or a SimpleGraph instance
            if(!empty($chunk) && !empty($chunk->_index)){
                $numberofchunks++;    
            }
            
            //debug
            //if ($numberofchunks > 0)
            //    break;

        }
        $this->l->cleanUp();
        $duration = microtime(true) - $start;
        $this->log[] = "Loaded $numberofchunks chunks in the store in " . $duration . "s.";
        return json_encode($this->log);
    }

}
