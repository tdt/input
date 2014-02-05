<?php

namespace tdt\input\emlp\load;

abstract class ALoader{

	protected $loader;

    public function __construct($loader){
        $this->loader = $loader;
    }

    abstract public function execute(&$chunk);

    /**
     * Clean up is called after the execute() function is performed.
     */
    public function cleanUp(){

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
