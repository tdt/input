<?php

namespace Tdt\Input\ETL;

/**
 * This class kickstarts and completes the ETL sequence.
 *
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 * @author Pieter Colpaert <pieter@okfn.be>
 */

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class JobExecuter
{
    private $job;
    private $command;

    /**
     * Create a new job with emlp relations
     */
    public function __construct($job, $command)
    {
        $this->job = $job;
        $this->command = $command;
    }

    /**
     * Execute the job
     */
    public function execute()
    {
        // Fetch the extractor, mapper (optional), loader and publisher (optional)
        // and use as constructor variables for the empl wrappers
        $extractor_model = $this->job->extractor()->first();
        $extractor = $this->getExecuter($extractor_model);

        $loader_model = $this->job->loader()->first();
        $loader = $this->getExecuter($loader_model);

        // Register the start of the execution
        $start = microtime(true);

        // Keep track of the number of objects that were processed
        $numberofobjects = 0;

        // Create the job id
        $id = $this->job->collection_uri . '/' . $this->job->name;
        $timestamp = date('d-m-Y H:i:s');

        // Log the start of the entire emlp execution
        $this->log("Started executing the job identified by $id at $timestamp.");

        // While the extractor reads objects, keep executing the eml sequence
        $loaded_objects = 0;

        while ($extractor->hasNext()) {
            $chunk = $extractor->pop();

            $success = $loader->execute($chunk);

            if ($success) {
                $loaded_objects++;
            }
        }

        // Clean up after loader execution unless no new objects were loaded
        if ($loaded_objects == 0) {
            $loader->cleanUp();
        }

        $duration = round(microtime(true) - $start, 2);

        $this->log("Extracted and loaded a total of $loaded_objects objects from the data source in " . $duration . " seconds.");

        // Execute the publisher if present ( optional )
        if (!empty($publisher)) {
            $publisher->execute();
        }

        $timestamp = date('d-m-Y H:i:s');
        $this->log("Ended job execution at $timestamp.");
    }

    /**
     * Retrieve the executer for a certain model of the emlp
     *
     * @return mixed
     */
    private function getExecuter($model)
    {

        if (empty($model)) {
            return $model;
        }

        $class = get_class($model);

        $pieces = explode('\\', $class);

        // Convert the model name to ucfirst namespace conventions
        $model_name = '';

        foreach ($pieces as $class_piece) {
            $model_name .= ucfirst($class_piece) . '\\';
        }

        $model_name = rtrim($model_name, '\\');

        $executer = 'Tdt\\Input\\ETL\\' . $model_name;

        if (!class_exists($executer)) {
            $model_class = get_class($model);

            // This error shouldn't occur when validation has returned true
            // If this fails, it means we did something wrong
            \App::abort(500, "The executer ($executer) was not found for the corresponding model $model_class).");
        }

        $executer = new $executer($model, $this->command);
        $executer->init();

        return $executer;
    }

    /**
     * Log something to the output
     */
    protected function log($message)
    {
        $class = explode('\\', get_called_class());
        $class = end($class);

        $this->command->info("JobExecuter: " . $message);

        $log_system = \Config::get('input::joblog.system');

        if ($log_system != 'cli') {
            \Log::info($message);
        }
    }
}
