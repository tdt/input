<?php

abstract class AMapper{
    protected $config;

    public function __construct($config){
        $this->config= $config;        
    }   

    abstract public function execute(&$chunk);

}
