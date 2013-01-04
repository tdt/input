<?php

/* 
 * This is the file which will receuve a call to start processing an ETML
 *
 */

set_include_path( get_include_path() . PATH_SEPARATOR . "../../" );
include_once("framework/AutoInclude.class.php");
include_once("framework/Log.class.php");
include_once("includes/EasyRdf.php");

if(!isset($argv[1])){
    Log::getInstance()->logCrit("wrong usage of input.php");
    echo "Usage: php input.php configname\nThe configname is defined in custom/input.ini";
    exit();
}

if(!file_exists("../../custom/input.ini")){
    Log::getInstance()->logCrit("Your config file does not exist.");
    echo "Your config file does not exist.";
    exit();
}

$input = parse_ini_file("../../custom/input.ini", true);
//check if resource exists
if(!isset($input[$matches[1]])){
    Log::getInstance()->logCrit("Your config file does not exist.");
    echo "Your config file does not exist.";
    exit();
}

$input = $input[$matches[1]];
