<?php

namespace tdt\input;
use JsonSchema\Validator;

abstract class AExtractor{
    
    protected $config;
    
    /**
     * Constructs the extractor according to a config, and opens the right handles.
     */
    public function __construct($config){
        
        $this->config = $config;
        $this->open($config["source"]);
    }

    public function __destruct(){
        $this->close();
    }
    

    /**
     * Preparatory work before starting to process the file. This function is called from the constructor of this class
     */
    abstract protected function open($file);
    
    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    abstract public function hasNext();


    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    abstract public function pop();


    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    abstract protected function close();


}
