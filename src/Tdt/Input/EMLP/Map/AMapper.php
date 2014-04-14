<?php

namespace Tdt\Input\EMLP\Map;

abstract class AMapper
{

    protected $mapper;
    protected $command;

    public function __construct($mapper, $command)
    {

        $this->mapper = $mapper;
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
     * Log something to the output
     */
    protected function log($message, $type = 'info')
    {

        $class = explode('\\', get_called_class());
        $class = end($class);

        $prefix = "Mapper[" . $class . "]: ";
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
    }
}
