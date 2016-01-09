<?php

namespace Tdt\Input\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearBeanstalk extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'input:clearqueue';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear a Beanstalkd queue, by deleting all pending jobs.';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Defines the arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return array(
            array('queue', InputArgument::OPTIONAL, 'The name of the queue to clear.'),
        );
    }
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $queue = ($this->argument('queue')) ? $this->argument('queue') : \Config::get('queue.connections.beanstalkd.queue');

        $this->info(sprintf('Clearing queue: %s', $queue));

        $pheanstalk = \Queue::getPheanstalk();
        $pheanstalk->useTube($queue);
        $pheanstalk->watch($queue);

        while ($job = $pheanstalk->reserve(0)) {
            $pheanstalk->delete($job);
        }

        // Set the flag of the jobs
        $jobs = \Job::all();

        foreach ($jobs as $job) {
            $job->added_to_queue = false;
            $job->save();
        }

        $this->info('The Beankstalk queue has been cleared.');
    }
}
