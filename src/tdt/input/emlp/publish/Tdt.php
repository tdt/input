<?php

namespace tdt\input\emlp\publish;

class Tdt{

    public function __construct($publisher){
        $this->publisher = $publisher;
    }

    /**
     * Publish the loaded data structure to the datatank
     */
    public function execute(){


        $this->log("Executing... TODO");
    }

    /**
     * Log something to the output
     */
    protected function log($message){

        $class = explode('\\', get_called_class());
        $class = end($class);

        echo "Loader[" . $class . "]: " . $message . "\n";
    }
}
