<?php
namespace tdt\input;

abstract class ALoader{
    protected $config;

    public function __construct($config){
        $this->config= $config;        
    }
    

    abstract public function execute(&$chunk);

}
