<?php

/**
 * TDTInput is the main model class
 * @author: Pieter Colpaert
 * @author: Miel Vander Sande
 */

namespace tdt\input;

class Input {

    //Extractor, Mapper, Loader
            private $e, $m, $l;
    //$ts is an array of transformers, as more transformations can be 
    private $ts;

    /**
     * Reads the input.ini file and initiates all classes according to their configuration
     */
    public function __construct($config) {

        if (!isset($config["extract"]))
            throw new \Exception('Extract method not set in config');

        //parse ini file for the extractor and create an instance of the right class
        $extractmethod = $config["extract"];
        
        $extractorclass = "tdt\\input\\extract\\" . $extractmethod;
        $this->e = new $extractorclass($config);

        //parse ini files for the transformers
        $this->ts = array();

        //parse ini file for the mapper
        $mapmethod = "tdt\\input\\map\\" . $config["map"];
        $this->m = new $mapmethod($config);

        //parse ini file for the loader
        $loadclass = "tdt\\input\\load\RDF";
        $this->l = new $loadclass($config);
    }

    /**
     * Execute our model according to the configuration parsed in the constructor
     */
    public function execute() {
        \tdt\framework\Log::getInstance()->logInfo("Starting the extractor");
        $start = microtime(true);
        $numberofchunks = 0;

        while ($this->e->hasNext()) {
            //1. EXTRACT
            $chunk = $this->e->pop();

            //2. TRANSFORM
            foreach ($this->ts as $t) {
                $chunk = $t->execute($chunk);
                \tdt\framework\Log::getInstance()->logInfo("Transform: ", $chunk);
            }

            //3. MAP
            if (!is_null($this->m)) {
                $chunk = $this->m->execute($chunk);
            }

            //4. LOAD
            if (!is_null($this->l)) {
                $this->l->execute($chunk);
            }

            $numberofchunks++;
        }

        $duration = microtime(true) - $start;
        $msg = "Loaded $numberofchunks chunks in the store in $duration ms";
        echo $msg;
        tdt\framework\Log::getInstance()->logInfo($msg);
    }

}
