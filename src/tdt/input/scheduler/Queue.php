<?php

namespace tdt\input\scheduler;
use RedBean_Facade as R;
/**
 * A recurring job queue
 *
 */
class Queue {

    public function __construct(array $db){
        R::setup($db["system"] . ":host=" . $db["host"] . ";dbname=" . $db["name"], $db["user"], $db["password"]);
;
    }

    /**
     * Pop() executes all elements which are due.
     * @return an id of a job or FALSE if empty
     */
    public function pop(){
        $all = R::findAll('job','timestamp < NOW()');
        foreach($all as $key => $val){
            $configname = $val->job;
            // I'm not sure what to do here yet - exec the job in the background or just create an instance of Input and executing the job
            // exec("php Worker.php '" . $configname . "' &");
        }
    }

    /**
     * Adds a job to the queue
     * @return the id of the job
     */
    public function push($jobcmd,$timestamp){
        $job = R::dispense('job');
        $job->job = $jobcmd;
        $job->timestamp = $timestamp;
        return R::store($job);
    }

    public function delete($id){
        $job = R::load('job',$id);
        R::trash($job); 
    }
    
    public function showAll(){
        $all = R::findAll('job','');
        return $all;
    }
    
}