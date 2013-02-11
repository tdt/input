<?php

namespace tdt\input\scheduler;

use RedBean_Facade as R;

/**
 * Schedule is a class which can be executed.
 */
class Schedule{

    private $db, $queue;

    public function __construct($db){
        $this->db = $db;
        $this->queue = new Queue($db);
        //search for the occurence of the job
        R::setup($this->db["system"] . ":host=" . $this->db["host"] . ";dbname=" . $this->db["name"], $this->db["user"], $this->db["password"]);
    }

    /**
     * Executes all the jobs in the queue
     */
    public function execute(){
        while($this->queue->hasNext()){
            $job = $this->queue->pop();
            //execute job using the configuration in the database
            echo $job . "\n";
            $this->reSchedule($job);
        }
    }

    /**
     * Reschedule a job already in the job catalogue
     * job is a name of a job
     */
    private function reSchedule($job, $timestamp){
        $jobr = R::findOne('job','name = ?' , array($job));
        $this->queue->push($job,date('U') + $jobr->occurence);
    }
    
    private function add($jobtoadd){
        $job = R::dispense('job');
        $job->name = $jobtoadd->name;
        $job->occurence = $jobtoadd->occurence;
        $job->config = $jobtoadd->config;
        return R::store($job);
    }    

}