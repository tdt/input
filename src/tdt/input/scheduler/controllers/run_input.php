<?php

/**
 * This script kickstarts an EML sequence, it expects the jobname to be passed as the first argument.
 */

define('VENDORPATH', __DIR__ . "/../../../../../../../");
define('APPPATH', __DIR__ . "/../../../../../../../../app/");

require_once VENDORPATH . "autoload.php";

// Load the configurator
require_once APPPATH . "core/configurator.php";

// Load the configuration wrapper
require_once APPPATH . "core/Config.php";

// Load the start controllers
require_once APPPATH . "controllers/ErrorController.class.php";
require_once APPPATH . "controllers/DocumentationController.class.php";
require_once APPPATH . "controllers/RedirectController.class.php";

use tdt\input\scheduler\Schedule;
use tdt\input\Input;
use tdt\input\scheduler\controllers\InputResourceController;
use tdt\core\formatters\FormatterFactory;
use tdt\exceptions\TDTException;
use app\core\Config;

if(empty($argv[1])){
    // Log this error.
    throw new \Exception("Pass along a job-name with the script.");
}

$resource = $argv[1];

// Get the configuration of the database.
$config_files = array(
    "db",
    "general",
);

$object = new \stdClass();

$config = Configurator::load($config_files);

$s = new Schedule($config['db']);

$object->job = $s->getJob($resource);

unset($object->job["id"]);
if(isset($object->job["map"])){
    unset($object->job["map"]["id"]);
    unset($object->job["map_id"]);
}

$base_uri = $object->job['map']['datatank_uri'] . $object->job['name'];

$object->job['load']['base_uri'] = $base_uri;

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
$log_path = $config['general']['logging']['path'];
$job["log_path"] = $log_path;
$input = new Input($job);
$input->execute();