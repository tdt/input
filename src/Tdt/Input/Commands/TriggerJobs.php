<?php

namespace Tdt\Input\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Tdt\Input\Controllers\InputController;
use Tdt\Input\ETL\JobExecuter;
use Carbon\Carbon;

/**
 * The TriggerJobs class holds the functionality to execute a job
 *
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class TriggerJobs extends Command
{
    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'input:triggerjobs';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Trigger the jobs that are due.";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire()
    {
        $jobs = \Job::whereNotNull('date_executed')
                ->get();

        foreach ($jobs as $job) {
            if (empty($job->added_to_queue) || !$job->added_to_queue) {
                // Check if they are due
                $now = Carbon::now();

                $exec_time = date('Y-m-d', $job->date_executed);
                
                $job_exec_time = new Carbon($exec_time);

                $push_to_q = false;
                $diff_in_time_string = '';

                switch ($job->schedule) {
                    case 'half-daily':
                        $diff = $now->diffInHours($job_exec_time);

                        $diff_in_time_string = $diff . ' hours';

                        if ($diff >= 6) {
                            $push_to_q = true;
                        }
                        break;
                    case 'daily':
                        $diff = $now->diffInDays($job_exec_time);

                        $diff_in_time_string = $diff . ' days';

                        if ($diff >= 1) {
                            $push_to_q = true;
                        }
                        break;
                    case 'weekly':
                        $diff = $now->diffInweeks($job_exec_time);

                        $diff_in_time_string = $diff . ' weeks';

                        if ($diff >= 1) {
                            $push_to_q = true;
                        }
                        break;
                    case 'monthly':
                        $diff = $now->diffInMonths($job_exec_time);

                        $diff_in_time_string = $diff . ' months';

                        if ($diff >= 1) {
                            $push_to_q = true;
                        }
                        break;
                }

                $job_name = $job->collection_uri . '/' . $job->name;

                if ($push_to_q) {
                    $job->added_to_queue = true;
                    $job->save();

                    $this->info("The job ($job_name) has been added to the queue.");

                    $this->executeCommand($job->collection_uri . '/' . $job->name);
                } else {
                    $this->info("The job $job_name has not been added to the queue, time difference was $diff_in_time_string");
                }

            }
        }
    }

    private function executeCommand($job_name)
    {
        \Queue::push(function ($job) use ($job_name) {
            \Artisan::call('input:execute', ['jobname' => $job_name]);

            $job->delete();
        });
    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
