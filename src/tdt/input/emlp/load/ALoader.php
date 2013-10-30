<?php

namespace tdt\input\emlp\load;

abstract class ALoader{

	protected $loader;

    public function __construct($loader){
        $this->loader = $loader;
    }

    abstract public function execute(&$chunk);

    // When not implemented, do nothing
    public function cleanUp(){

    }
}
