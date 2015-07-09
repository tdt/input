<?php

namespace Tdt\Input\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Tdt\Input\Controllers\InputController;
use Tdt\Input\ETL\JobExecuter;

/**
 * The ExecuteJobCommand class holds the functionality to execute a job
 *
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class ExecuteJob extends Command
{

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
    public function fire()
    {
        // Check for a list option
        $list = $this->option('list');

        if (empty($list)) {

            $job_name = $this->argument('jobname');

            list($collection_uri, $name) = InputController::getParts($job_name);

            // Check if the job exists
            $job = \Job::where('name', '=', $name)
                       ->where('collection_uri', '=', $collection_uri)
                       ->first();

            if (empty($job)) {
                $this->error("The job with identified by: $job_name could not be found.\n");
                exit();
            }

            $this->line('The job has been found.');

            $job_exec = new JobExecuter($job, $this);
            $job_exec->execute();

        } else {

            $jobs = \Job::all(['name', 'collection_uri'])->toArray();

            if (!empty($jobs)) {

                $this->info("=== Job names ===");

                foreach ($jobs as $job) {
                    $this->info($job['collection_uri'] . '/' . $job['name']);
                }
            } else {
                $this->info("No jobs found.");
            }
        }
    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('jobname', InputArgument::OPTIONAL, 'Full name of the job that needs to be executed. (the uri that was given to PUT the meta-data for this job).'),
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('list', 'l', InputOption::VALUE_NONE, 'Display a list of all available jobs.', null),
        );
    }
}
