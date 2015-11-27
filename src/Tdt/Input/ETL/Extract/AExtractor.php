<?php

namespace Tdt\Input\ETL\Extract;

abstract class AExtractor
{

    protected $extractor;
    protected $command;

    public function __construct($extractor, $command)
    {
        $this->extractor = $extractor;
        $this->command = $command;
        $this->open();
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Initialize function provides room for custom pre-execution initialization.
     */
    public function init()
    {

    }

    /**
     * Preparatory work before starting to process the file. This function is called from the constructor of this class
     */
    abstract protected function open();

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    abstract public function hasNext();


    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    abstract public function pop();


    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    abstract protected function close();

    /**
     * Log something to the output
     * Always log to the CLI, but take into account
     * to log into database systems if configured
     */
    protected function log($message, $type = 'info')
    {
        $class = explode('\\', get_called_class());
        $class = end($class);

        $prefix = "Extractor[" . $class . "]: ";
        $message = $prefix . $message;

        switch ($type) {
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

        $log_system = \Config::get('input::joblog.system');

        if ($log_system != 'cli') {
            \Log::info($message);
        }
    }
}
