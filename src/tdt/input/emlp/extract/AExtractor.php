<?php

namespace tdt\input\emlp\extract;

abstract class AExtractor{

    protected $extractor;

    public function __construct($extractor){
        $this->extractor = $extractor;
        $this->open();
    }

    public function __destruct(){
        $this->close();
    }


    /**
     * Preparatory work before starting to process the file. This function is called from the constructor of this class
     */
    abstract protected function open();

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

    /**
     * Log something to the output
     */
    protected function log($message){

        $class = explode('\\', get_called_class());
        $class = end($class);

        echo "Extractor[" . $class . "]: " . $message . "\n";
    }
}
