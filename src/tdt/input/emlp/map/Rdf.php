<?php

namespace tdt\input\emlp\map;

use tdt\streamingrdfmapper\vertere\Vertere;
use tdt\streamingrdfmapper\StreamingRDFMapper;

class Rdf extends AMapper {

    private $mapping_processor;
    private $map_count;

    function __construct($model){

        parent::__construct($model);

        // Keep track of the number of chunks mapped
        $this->map_count = 1;

        $mapfile = $this->mapper->mapfile;

        $this->log("Retrieving the mapping on location $mapfile.");
        $mapping_file = file_get_contents($mapfile);

        // TODO make the type a variable in the model
        $mapping_type = "Vertere";

        if(!$mapping_file){
            $this->log("The mapping file could not be retrieved on location $this->mapper->mapfile.");
        }

        $this->mapping_processor = new StreamingRDFMapper($mapping_file, $mapping_type);
    }


    /**
     * Execute the mapping of a chunk of data
     */
    public function execute(&$chunk) {

        $this->log("Executing mapping rules for data chunk $this->map_count.");

        // Retrieve an instance of an EasyRDFGraph
        $rdf_graph = $this->mapping_processor->map($chunk, true);
        $this->map_count++;

        //TODO how to know if the mapping was succesfull?
        return $rdf_graph;
    }
}
