<?php

namespace Tdt\Input\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Tdt\Input\Controllers\InputController;
use Tdt\Input\ETL\JobExecuter;
use Monolog\Handler\MongoDBHandler;
use MongoClient;
use Elastica\Client;
use Monolog\Handler\ElasticSearchHandler;

/**
 * The ExecuteJob class holds the functionality to execute a job
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
    protected $description = "Execute a defined ETL job.";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire()
    {
        // Check for a list option
        $list = $this->option('list');
        $job_name = $this->argument('jobname');

        if (empty($list) && !empty($job_name)) {
            $inputController = new InputController();

            list($collection_uri, $name) = $inputController->getParts($job_name);

            // Check if the job exists
            $job = \Job::where('name', '=', $name)
                       ->where('collection_uri', '=', $collection_uri)
                       ->first();

            if (empty($job)) {
                $this->error("The job with identified by: $job_name could not be found.\n");
                exit();
            }

            $this->info('The job has been found.');

            // Configure a log handler if configured
            $this->addLogHandler();

            \Log::info("Executing job $name");

            $job_exec = new JobExecuter($job, $this);
            $job_exec->execute();

            $job->date_executed = time();
            $job->added_to_queue = false;
            $job->save();
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

    /**
     * Add a log handler based on the input configuration to log job info too
     *
     * @return void
     */
    private function addLogHandler()
    {
        $log_system = \Config::get('input::joblog.system');

        if ($log_system == 'mongodb') {
            $mongo_config = \Config::get('input::joblog.databases.mongodb');

            $username = $mongo_config['username'];
            $password = $mongo_config['password'];

            $auth = [];

            if (!empty($username)) {
                $auth['username'] = $username;

                $password = $mongo_config['password'];

                if (!empty($password)) {
                    $auth['password'] = $password;
                }
            }

            $connString = 'mongodb://' . $mongo_config['host'] . ':' . $mongo_config['port'] . '/logs';

            $mongoHandler = new MongoDBHandler(
                new MongoClient($connString, $auth),
                $mongo_config['database'],
                $mongo_config['collection']
            );

            \Log::getMonolog()->pushHandler($mongoHandler);
        } elseif ($log_system == 'elasticsearch') {
            $es_config = \Config::get('input::joblog.databases.elasticsearch');

            $username = $es_config['username'];
            $pw = $es_config['password'];
            $host = $es_config['host'];

            if (!empty($username) && !empty($pw)) {
                $auth_header_val = 'Basic ' . base64_encode($username . ':' . $pw);
                $auth_header = array('Authorization' => $auth_header_val);
            }

            $config['host'] = $host;
            $config['port'] = $es_config['port'];
            $config['headers'] = $auth_header;

            $index = $es_config['index'];
            $type = $es_config['type'];

            $client = new Client($config);
            $handler = new ElasticSearchHandler($client, ['index' => $index, 'type' => $type]);

            \Log::getMonolog()->pushHandler($handler);
        }
    }
}
