<?php

// The mongodb collection to write the job logs to
return array(
    'server' => 'mongodb://localhost:27017',
    'database' => 'database',
    'collection'=> 'job_logs',
    'time_zone' => 'GMT+1',
    'datetime_format' => 'Y-m-d H:i:s'
);
