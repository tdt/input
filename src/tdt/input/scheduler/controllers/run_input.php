<?php

/**
 * This script kickstarts an EML sequence, it expects the jobname to be passed as the first argument.
 */ 

if(empty($argv[1])){
    // Log this error.
    throw new Exception("Pass along a job-name with the script.");
}

// the second variable is optional and asks to pass along the path to the autoloader of tdtinput.
if(empty($argv[2])){
    $autoload = __DIR__ . '/../../../../../../../autoload.php';
}else{
    $autoload = $argv[2];
}
echo $autoload;
require $autoload;

use tdt\input\scheduler\Schedule;
use tdt\input\Input;
use tdt\input\scheduler\controllers\InputResourceController;
use tdt\core\formatters\FormatterFactory;
use tdt\exceptions\TDTException;
use app\core\Config;

$resource = $argv[1];

$cont = new InputResourceController();
$s = new Schedule(InputResourceController::getDBConfig());  
$object->job = $s->getJob($resource);   
                
unset($object->job["id"]);
if(isset($object->job["map"])){
    unset($object->job["map"]["id"]);
    unset($object->job["map_id"]);
}
unset($object->job["load"]["id"]);
unset($object->job["extract"]["id"]);

unset($object->job["load_id"]);
unset($object->job["extract_id"]);

$object->job = array_filter($object->job);

$object->job["extract"] = array_filter($object->job["extract"]);
$object->job["map"] = array_filter($object->job["map"]);
$object->job["load"] = array_filter($object->job["load"]);

if(empty($object->job)){
    throw new TDTException("404",array($resource));
}

ignore_user_abort(true);
set_time_limit(0);
//convert object to array
$job = json_decode(json_encode($object->job) ,true);                
$input = new Input($job);                
echo $input->execute();

