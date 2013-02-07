<?php

<<<<<<< HEAD
namespace tdt\input\scheduler;

=======
namespace tdt/input/scheduler;
>>>>>>> 45e471a107b3e30c3f1460d2647ae677f11ce6aa
/**
 * This class looks whether the queue contains things to execute
 */
class Worker{
    private $queue;
    
    /**
     * @param $config is an array with db config
     */
    public function __construct(array $config){
        $this->queue = new Queue($config);
    }

    public function execute(){
        while($q->hasNext()){
            $job = $q->pop();
            //execute job using the configuration in the database
            echo $job . "\n";
            $this->schedule($job);
        }
    }


    /**
     * Adds a new job to the database
     */
    private function schedule($job){
        //search for the occurence of the job
        
        
    }
}

