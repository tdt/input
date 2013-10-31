<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use tdt\input\controllers\InputController;
use tdt\input\emlp\JobExecuter;

/**
 * The ExecuteJobCommand class holds the functionality to execute a job
 *
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class ExecuteJobCommand extends Command {

    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'input:execute';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Execute a defined extract-map-load-publish job.";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire(){

        $job_name = $this->argument('jobname');

        list($collection_uri, $name) = InputController::getParts($job_name);

        // Check if the job exists
        $job = \Job::whereRaw("? like CONCAT(collection_uri, '/', name , '/', '%')", array($job_name . '/'))->first();

        if(empty($job)){
            $this->error("The job with identified by: $job_name could not be found.\n");
        }

        $this->line('The job has been found.');

        $job_exec = new JobExecuter($job);
        $job_exec->execute();

    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments(){
        return array(
            array('jobname', InputArgument::REQUIRED, 'Full name of the job that needs to be executed. (the uri that was given to PUT the meta-data for this job).'),
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions(){
        return array(

        );
    }
}
