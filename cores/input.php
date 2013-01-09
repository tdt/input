<?php

/* 
 * This is the file which will receive a call to start processing an ETML
 */
set_include_path( get_include_path() . PATH_SEPARATOR . "../" );
chdir("../");
require("vendor/autoload.php");

include_once("cores/input/model/TDTInput.class.php");
if(!isset($argv[1])){
    tdt\framework\Log::getInstance()->logCrit("wrong usage of input.php");
    echo "Usage: php input.php config name\nThe config name is defined in custom/input.ini\n";
    exit();
}

$configname = $argv[1];

if(!file_exists("custom/input.ini")){
    tdt\framework\Log::getInstance()->logCrit("Your config file does not exist.");
    echo "Your config file (input.ini) does not exist.\n";
    exit();
}

Log::getInstance()->logInfo("Started input for file...",$configname);

$input = parse_ini_file("custom/input.ini", true);
//check if resource exists
if(!isset($input[$configname])){
    tdt\framework\Log::getInstance()->logCrit("Your config file does not exist.");
    echo "Your config (input.ini) file does not contain your config of $configname.\n";
    exit();
}

$inputconfig = $input[$configname];

try{
    $model = new TDTInput($inputconfig);
    $model->execute();
}
catch(Exception $e){
    tdt\framework\Log::getInstance()->logCrit("ETML Failed: " . $e->getMessage());
}


