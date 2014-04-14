<?php

namespace Tdt\Input\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;
use Tdt\Input\Controllers\InputController;

class Import extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'input:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import job definitions from a JSON file.';

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
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // Get the input file from the arguments, if none specified use the default file path from the export command
        $file = $this->argument('file');

        if (empty($file)) {
            $file = Export::getExportFile();
        }

        $safe = $this->option('safe');

        // Get the contents from the file if it exists
        if (File::exists($file)) {

            $content = json_decode(File::get($file), true);

            // If the content is legit, proceed to make the calls to the input endpoint
            if ($content) {

                foreach ($content as $identifier => $job_definition) {

                    // If the safe option is passed, prompt the user with the identifier and body
                    if ($safe) {
                        if (!$this->confirm("A job with identifier $identifier is about to be added, are you sure about this? [y|n]")) {
                            $this->info("The job with identifier $identifier was prevented of being added.");
                            break;
                        }
                    }

                    $this->updateRequest('PUT', array(), $job_definition);

                    $response = InputController::createJob($identifier);

                    $status_code = $response->getStatusCode();

                    if ($status_code == 200) {
                        $this->info("A new definition with identifier ($identifier) was succesfully added.");
                    } else {
                        $this->error("A status of $status_code was returned when adding $identifier, check the logs for indications of what may have gone wrong.");
                    }
                }

            } else {
                $this->error("We failed to extract the input jobs from the json, make sure the JSON content is valid.");
                die;
            }

        } else {
            $this->error("We couldn't find the file ($file) containing the input jobs, make sure the path is reachable for the datatank.");
            die;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('file', InputArgument::OPTIONAL, 'Path to the JSON file that contains the job definitions. Defaults to the ' . Export::getExportFile() . ' file.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('safe', 's', InputOption::VALUE_NONE, "Safe mode will prompt the user for every job it will add, and ask if it's ok to perform the addition.", null),
        );
    }

    /**
     * Custom API call function
     */
    public function updateRequest($method, $headers = array(), $data = array())
    {

        // Set the custom headers.
        \Request::getFacadeRoot()->headers->replace($headers);

        // Set the custom method.
        \Request::setMethod($method);

        // Set the content body.
        if (is_array($data)) {
            \Input::merge($data);
        }
    }
}
