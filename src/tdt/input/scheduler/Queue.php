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
    }

    public function hasNext(){
        $all = R::find('queue','timestamp < ?',array( (int)date('U') ));
        return sizeof($all)>0;
    }

    /**
     * Pop() executes all elements which are due.
     * @return an id of a job or FALSE if empty
     */
    public function pop(){
        $job = R::findOne('queue','timestamp < ?' ,array( (int)date('U')));
        $configname = $job->job;
        R::trash($job);
        return $configname;
    }

    /**
     * Adds a job to the queue
     * @return the id of the job
     */
    public function push($jobcmd,$timestamp){
        $job = R::dispense('queue');
        $job->job = $jobcmd;
        $job->timestamp = $timestamp;
        return R::store($job);
    }

    public function delete($id){
        $job = R::load('queue',$id);
        R::trash($job);
    }
    
    public function getAll(){
        $all = R::findAll('queue',' Order by timestamp ASC');
        return $all;
    }
    
}