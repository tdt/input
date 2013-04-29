<?php

namespace tdt\input\scheduler;

use RedBean_Facade as R;
use tdt\input\Input;
use JsonSchema\Validator;
use tdt\exceptions\TDTException;

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
        R::dependencies(array('extract' => array('job'), 'map'=>array('job'), 'load' => array('job')));
    }

    /**
     * Executes all the jobs in the queue that are due
     */
    public function execute(){
        while($this->queue->hasNext()){
            $jobname = $this->queue->pop();
            echo "Executing: " . $jobname . "\n <br/>";
            echo "#####################################\n<br/>";
            $job = $this->getJob($jobname);
            //execute job using the configuration in the database
            $input = new Input($job,$this->db);
            try{
                $input->execute();
            }catch(Exception $e){
                echo $e->getMessage() . "\n<br/>";
            }
            $this->reSchedule($jobname);
            echo "$jobname rescheduled";
            echo "<br/><br/>\n\n";
        }
    }

    /**
     * Reschedule a job already in the job catalogue
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

    private function validateConfig($config,$schemapath){
        $validator = new Validator();
        $schema = file_get_contents($schemapath,true);
        $validator->check(json_decode(json_encode($config),false), json_decode($schema));
        if (!$validator->isValid()) {
            echo "The given configuration file for the schedule does not validate. Violations are (split with -- ):\n";
            foreach ($validator->getErrors() as $error) {
                echo sprintf("[%s] %s -- ",$error['property'], $error['message']);
            }
            set_error_header(462, "JSON invalid");
            die();
        }
    }
    

    /**
     * Adds a job description to the system
     * Validation is done by a json schema which you find in jobs.schema.json
     * @param jobtoadd is an array with properties: name, occurence and config. Config in itself is a new array containing the ETML recipe
     */
    public function add($jobtoadd,$overwrite = false){
        if(isset($jobtoadd->job)){
            $jobtoadd = $jobtoadd->job;
        }
        $this->validateConfig($jobtoadd,"job.schema.json");
        $this->validateConfig($jobtoadd->extract, $jobtoadd->extract->type . ".extract.schema.json");
        if(isset($jobtoadd->map))
            $this->validateConfig($jobtoadd->map,$jobtoadd->map->type .".map.schema.json");
        $this->validateConfig($jobtoadd->load, $jobtoadd->load->type .".load.schema.json");
        
        $existingjobs = R::findOne('job','name = ?',array($jobtoadd->name));
        if(empty($existingjobs)){
            $job = R::dispense('job');
            $job->name = $jobtoadd->name;
            $job->occurence = $jobtoadd->occurence;
            $extract = R::dispense('extract');
            foreach(get_object_vars($jobtoadd->extract) as $k=>$v){
                $extract->$k = $v;
            }
            $job->extract = $extract;
            
            $map = R::dispense('map');
            if(isset($jobtoadd->map)){
                foreach(get_object_vars($jobtoadd->map) as $k=>$v){
                    $map->$k = $v;
                }
            }
            $job->map = $map;
            
            $load = R::dispense('load');
            foreach(get_object_vars($jobtoadd->load) as $k=>$v){
                $load->$k = $v;
            }
            $job->load = $load;

            $id = R::store($job);
            $this->schedule($jobtoadd->name);
            return $id;
        }else if($overwrite){
            $this->delete($jobtoadd->name);
            $this->add($jobtoadd);
        }else{
            throw new TDTException(452,array("Job name already exists"));
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
        if(empty($job)){
            return null;
        }
        $job->extract;
        $job->map;
        $job->load;
        return $job->export();
    }

    public function getAllNames(){
        $all = R::getAll("select name from job");
        return $all;
    }
    

    public function delete($jobname){
        $job = R::findOne('job',' name = ?',array($jobname));
        if(!empty($job)){
            $job->extract;
            $job->map;
            $job->load;
            $this->queue->deleteByName($jobname);
            R::trash($job);
        }
    }

}