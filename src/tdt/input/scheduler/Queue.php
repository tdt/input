<?php

namespace tdt\input\scheduler;

/**
 * A recurring job queue
 *
 */
class Queue {

    public function __construct(array $db){
        R::setup();
    }

    /**
     * Pop() executes all elements which are due.
     * @return an id of a job or FALSE if empty
     */
    public function pop(){
        $all = R::findAll('job','timestamp < NOW()');
        foreach($all as $key => $val){
            $configname = $val->job;
            exec("php Worker.php '" . $configname . "' &");
        }
    }

    /**
     * Adds a job to the queue
     */
    public function push($job,$timestamp){
        

    }

    public function delete($id){
        
    }
    
    public function showAll(){
        
    }
    
}