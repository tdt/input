<?php

/* 
 * This is the file which will receive a call to start processing an ETML
 */
chdir("../");
set_include_path(get_include_path() . PATH_SEPARATOR . "../");
require("vendor/autoload.php");

if(!isset($argv[1])){
    echo "Usage: php input.php config name\nThe config name is defined in custom/input.ini\n";
    exit();
}

$configname = $argv[1];

if(!file_exists("examples/custom/input.ini")){
    echo "Your config file (input.ini) does not exist.\n";
    exit();
}

echo "Started input for file: " . $configname . "\n";

$input = parse_ini_file("examples/custom/input.ini", true);
//check if resource exists
if(!isset($input[$configname])){
    echo "Your config (input.ini) file does not contain your config of $configname.\n";
    exit();
}

$inputconfig = $input[$configname];

try{
    $model = new tdt\input\Input($inputconfig);
    $model->execute();
}
catch(Exception $e){
    echo "ETML Failed: " . $e->getMessage() . "\n";
}
