<?php
namespace tdt\input;

abstract class ALoader{
    public $log;
    
    public function __construct(&$log){
        $this->log = &$log;
    }
    
    abstract public function execute(&$chunk);
}
