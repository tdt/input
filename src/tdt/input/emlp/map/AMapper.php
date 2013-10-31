<?php

namespace tdt\input\emlp\map;

abstract class AMapper{

	protected $mapper;

	public function __construct($mapper){
		$this->mapper = $mapper;
	}

    abstract public function execute(&$chunk);

    /**
     * Log something to the output
     */
    protected function log($message){

        $class = explode('\\', get_called_class());
        $class = end($class);

        echo "Mapper[" . $class . "]: " . $message . "\n";
    }
}
