<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use tdt\input\controllers\InputController;

class Export extends Command {

    /**
     * The default file to write the export to.
     *
     * @var string
     */
    public static $EXPORT_FILE = 'jobs.json';

    /**
     * Return the path to the export file.
     *
     * @var string
     */
    public static function getExportFile(){
        return app_path() . "/commands/" . self::$EXPORT_FILE;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'input:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export job definitions to a json file. Can be used to import job definitions using input:import.';

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
        // Get the file option from the command line
        $file = $this->argument('file');

        if(empty($file)){
            $file = self::getExportFile();
        }

        // Get the jobid, if none is provided, return all of the jobs by default
        $jobid = $this->argument('jobid');
        $content = null;

        if(empty($jobid)){

            $jobs = \Job::all();

            $content = array();

            foreach($jobs as $job){
                $content[$job->collection_uri . '/' . $job->name] = $job->getAllProperties();
            }

            $content = json_encode($content);

        }else{

            $job = InputController::get($jobid);

            if(empty($job)){
                $this->error("No input job has been found with the given identifer ($jobid).");
                die;
            }

            $content[$job->collection_uri . '/' . $job->name] = $job->getAllProperties();
            $content = json_encode($content);
        }

        // Try to write the contens to the specified file
        try{
            file_put_contents($file, $content);
        }catch(Exception $e){
            $this->error("The contents could not be written to the file ($file).");
            die;
        }

        $this->info("The export has been written to $file.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('file', InputArgument::OPTIONAL, 'The file to write the JSON export to. Defaults to the app/commands folder', null),
            array('jobid', InputArgument::OPTIONAL, 'The identifier of the job to export, if empty all of the jobs will be exported.', null),
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

        );
    }

}