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
    
    //transformers, an array
    private $t;

    public $log;

    private $db;
    
    /**
     * Reads the input.ini file and initiates all classes according to their configuration
     * The configuration is not (yet) validated here.
     */
    public function __construct($config,$db = array()) {

        //set db up
        if(!empty($db)){
            $this->db = $db;
            R::setup($this->db["system"] . ":host=" . $this->db["host"] . ";dbname=" . $this->db["name"], $this->db["user"], $this->db["password"]);
        }

        //extractors
        $extractmethod = $config["extract"]["type"];
        $extract = $config["extract"];
        $load = $config["load"];
        $extractorclass = "tdt\\input\\extract\\" . $extractmethod;
        $this->e = new $extractorclass($extract,$this->log);
        
        //transformers
        $this->t = array();        
        if(is_array($config["transform"]) && !empty($config["transform"])){
            foreach($config["transform"] as $t){
                if(!empty($config["transform"]["type"])){
                    $transformclass = "tdt\\input\\transform\\" . $config["transform"]["type"];
                    $this->t[] = new $transformclass($config["transform"],$this->log);
                }
            }
        }

        // mapper
        if(!empty($config["map"]) && !empty($config["map"]["type"])){
            $map = $config["map"];
            $mapmethod = "tdt\\input\\map\\" . $config["map"]["type"];
            $this->m = new $mapmethod($map, $this->log);
        }

        // loader
        if(!empty($config["load"]) && !empty($config["load"]["type"])){
            $loadclass = "tdt\\input\\load\\" . $config["load"]["type"];
            $this->l = new $loadclass($load, $this->log);
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

                //1.5 TRANSFORM
                foreach($this->t as $t){
                    $chunk= $this->t->execute();
                }

                //2. MAP
                if (!empty($this->m)) {
                    $chunk = $this->m->execute($chunk);
                }

                //3. LOAD
                if (!empty($this->l)) {
                    $this->l->execute($chunk);
                }

                $numberofchunks++;
        }
        $this->l->cleanUp();
        $duration = microtime(true) - $start;
        $this->log[] = "Loaded $numberofchunks chunks in the store in " . $duration . "s.";
        echo json_encode($this->log);
    }

}
