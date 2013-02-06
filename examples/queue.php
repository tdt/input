<?php
/**
 *
 */
require_once("../vendor/autoload.php");

/**
 * This example will put something in the queue and get it out at a certain point in time
 */

$q = new \tdt\input\scheduler\Queue(array(
                                        "system"=>"mysql",
                                        "user" => "test",
                                        "name" => "test",
                                        "host" => "localhost",
                                        "password" => ""
                                    ));

//schedule for 1 second ago
$q->push("job1",date("U")-1);

//schedule for 1 second in the future
$q->push("job1",date("U")+1);


while($q->hasNext()){
    $job = $q->pop();
    //execute job
    echo $job . "\n";
    
}
