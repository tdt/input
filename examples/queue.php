<?php
/**
 *
 */
chdir("../");
set_include_path(get_include_path() . PATH_SEPARATOR . "../");
require_once("vendor/autoload.php");

/**
 * This example will put something in the queue and get it out at a certain point in time
 */
$q = new \tdt\input\scheduler\Queue(parse_ini_file("examples/custom/db.ini", false));

//schedule for 1 second ago
$q->push("job1",date("U")-1);

//var_dump($q->showAll());

while($q->hasNext()){
    $job = $q->pop();
    //execute job
    echo $job . "\n";
}

