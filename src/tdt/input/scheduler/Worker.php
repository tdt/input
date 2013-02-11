<?php

namespace tdt\input\scheduler;

/**
 * This class looks whether the queue contains things to execute
 */
class Worker{
    private $queue, $db;
    
    /**
     * @param $config is an array with db config
     */
    public function __construct(array $config){
        $this->db = $config;
    }

    public function execute(){
        $schedule = new Schedule($this->db);
        $schedule->execute();
    }
}
