<?php

namespace Tdt\Input\EMLP\Load;

abstract class ALoader
{

    protected $loader;
    protected $command;

    public function __construct($loader, $command)
    {

        $this->loader = $loader;
        $this->command = $command;
    }

    /**
     * Initialize function provides room for custom pre-execution initialization.
     */
    public function init()
    {

    }

    abstract public function execute(&$chunk);

    /**
     * Clean up is called after the execute() function is performed.
     */
    public function cleanUp()
    {

    }

    /**
     * Log something to the output
     */
    protected function log($message, $type = 'info')
    {
        $class = explode('\\', get_called_class());
        $class = end($class);

        $prefix = "Loader[" . $class . "]: ";
        $message = $prefix . $message;

        switch($type){

            case 'info':

                $this->command->info($message);
                break;
            case 'error':

                $this->command->error($message);
                break;
            default:

                $this->command->line($message);
                break;
        }

        // Also log this to the mongo collection
        $client = new \MongoClient(\Config::get('input::mongolog.server'));

        $collection = $client->selectCollection(\Config::get('input::mongolog.database'), \Config::get('input::mongolog.collection'));

        $log = array('message' => $message);

        // Add the identifier and the timestamp to the log document
        $log['execution_timestamp'] = $this->loader->execution_timestamp;

        $log['identifier'] = $this->command->argument('jobname');

        $collection->insert($log);
    }
}
