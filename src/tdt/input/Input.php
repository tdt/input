<?php

/**
 * Input is the main model class
 * @author Pieter Colpaert
 * @author Miel Vander Sande
 */

namespace tdt\input;
use JsonSchema\Validator;

class Input {

    //Extractor, Mapper, Loader
    private $e, $m, $l;

    /**
     * Reads the input.ini file and initiates all classes according to their configuration
     * The configuration is not (yet) validated here.
     */
    public function __construct($config,$db) {

        $extractmethod = $config["extract"]["type"];
        $extract = $config["extract"];
        $load = $config["load"];
        $extractorclass = "tdt\\input\\extract\\" . $extractmethod;
        $this->e = new $extractorclass($extract,$db);

        // mapper
        if(!empty($config["map"])){
            $map = $config["map"];
            $mapmethod = "tdt\\input\\map\\" . $config["map"]["type"];
            $this->m = new $mapmethod($map,$db);
        }

        // loader
        if(!empty($config["load"])){
            $loadclass = "tdt\\input\\load\\" . $config["load"]["type"];
            $this->l = new $loadclass($load,$db);
        }
    }
    

    /**
     * Execute our model according to the configuration parsed in the constructor
     */
    public function execute() {

        $start = microtime(true);
        $numberofchunks = 0;
        
        echo "Started ETML process\n";

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

            $numberofchunks++;
        }

        $duration = microtime(true) - $start;
        $msg = "Loaded $numberofchunks chunks in the store in " . $duration . "s. \n";
        echo $msg;
    }

}
