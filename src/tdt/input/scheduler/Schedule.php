<?php

namespace tdt\input\scheduler;

use RedBean_Facade as R;
use tdt\input\Input;

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
        R::dependencies(array('config' => array('job')));
    }

    /**
     * Executes all the jobs in the queue that are due
     */
    public function execute(){
        while($this->queue->hasNext()){
            $jobname = $this->queue->pop();
            $job = $this->getJob($jobname);
            $configbean = $job->config;
            $config = $configbean->export();
            //execute job using the configuration in the database
            $input = new Input(array_merge($config,$this->db));
            $input->execute();
            $this->reSchedule($jobname);
        }
    }

    /**
     * Reschedule a job already in the job catalogue
     * job is a name of a job
     */
    private function reSchedule($jobname){
        $job = R::findOne('job','name = ?' , array($jobname));
        $this->queue->push($jobname,date('U') + $job->occurence);
    }
   
    /**
     * Schedule a job for immeadiate execution
     * jobname is a name of a job
     */
    private function schedule($jobname){
        $job = R::findOne('job','name = ?' , array($jobname));
        $this->queue->push($jobname,date('U'));
    }

    /**
     * Adds a job description to the system
     * @param jobtoadd is an array with properties: name, occurence and config. Config in itself is a new array containing the ETML recipe
     */
    public function add($jobtoadd){
        $existingjobs = R::findOne('job','name = ?',array($jobtoadd["name"]));
        if(empty($existingjobs)){
            $job = R::dispense('job');
            $job->name = $jobtoadd["name"];
            $job->occurence = $jobtoadd["occurence"];
            $config = R::dispense('config');
            foreach($jobtoadd["config"] as $k=>$v){
                $config->$k = $v;
            }
            $job->config = $config;
            $id = R::store($job);
            $this->schedule($jobtoadd["name"]);
            return $id;
        }else{
            throw new \Exception("Job name already exists");
        }
    }

    /**
     * Get an array of all jobdescriptions in the system.
     * @return an array of jobs
     */
    public function getJobs(){
        $jobs = R::findAll('job');
        return $jobs;
    }

    public function getJob($jobname){
        $job = R::findOne('job',' name = ? ',array($jobname));
        $job->config;
        return $job->export();
    }

    public function getAllNames(){
        $all = R::getAll("select name from job");
        return $all;
    }
    

    public function delete($jobname){
        $job = R::findOne('job',' name = ?',array($jobname));
        $this->queue->deleteByName($jobname);
        R::trash($job);
    }

}