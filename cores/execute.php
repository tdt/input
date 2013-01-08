<?php

/* 
 * This is the file which will receive a call to start processing an ETML
 */

set_include_path( get_include_path() . PATH_SEPARATOR . "../" );
chdir("../");
include_once("framework/AutoInclude.class.php");
include_once("includes/Phive.php");

if(!isset($argv[0])){
    Log::getInstance()->logCrit("wrong usage of input.php");
    echo "Usage: php execute.php\nThe config name is defined in custom/input.ini\n";
    exit();
}


if(!file_exists("custom/input.ini")){
    Log::getInstance()->logCrit("Your config file does not exist.");
    echo "Your config file (input.ini) does not exist.\n";
    exit();
}

Log::getInstance()->logInfo("Started execution of the queue");

$input = parse_ini_file("custom/input.ini", true);

$dbh = new PDO('mysql:host='. Config::get("db","host") .';dbname='. Config::get("db","name"),Config::get("db","user"),Config::get("db","password"));

$q = new MySQLQueue($dbh,"test");



