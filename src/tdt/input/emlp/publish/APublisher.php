<?php

namespace tdt\input\emlp\publish;

abstract class APublisher{

    // Add the publisher model as a property
    protected $publisher;

    public function __construct($publisher){
        $this->publisher = $publisher;
    }

    /**
     * Execute the custom created publishing functionality
     */
    abstract public function execute();

    /**
     * Log something to the output
     */
    protected function log($message){

        $class = explode('\\', get_called_class());
        $class = end($class);

        echo "Loader[" . $class . "]: " . $message . "\n";
    }
}
