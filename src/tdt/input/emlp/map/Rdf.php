<?php

namespace tdt\input\emlp\map;

use tdt\streamingrdfmapper\vertere\Vertere;
use tdt\streamingrdfmapper\StreamingRDFMapper;

class Rdf extends AMapper {

    private $mapping_processor;

    function __construct($model){

        parent::__construct($model);

        $mapping_file = file_get_contents($this->mapper->mapfile);

        // TODO make variable
        $mapping_type = "Vertere";

        if(!$mapping_file){
            echo "The mapping file could not be retrieved on uri $this->mapper->mapfile .\n";
        }

        $this->mapping_processor = new StreamingRDFMapper($mapping_file, $mapping_type);
    }


    /**
     * Execute the mapping of a chunk of data
     */
    public function execute(&$chunk) {

        $start = microtime(true);

        // Retrieve an instance of an EasyRDFGraph
        $rdf_graph = $this->mapping_processor->map($chunk, true);

        //TODO how to know if the mapping was succesfull?
        return $rdf_graph;
    }
}
