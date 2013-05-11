<?php

namespace tdt\input;

abstract class ATransformer{
    protected $config;
    
    public function __construct($config, &$log){
        $this->config= $config;        
    }   

    abstract public function execute(&$chunk);

}

