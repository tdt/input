<?php

return array (

    /**
     * Define to where logs coming from job executions should go
     * cli is defaulted and will log everything to the console
     *
     * Fill in "mongodb" or "elasticsearch" as the value of "system" if you like to store the logs into
     * either system.
     *
     * Note: CLI output will not be shown when a job is executed by a Queue
     */
    'system' => 'mongodb',

    'databases' => array (
        'mongodb' => array (
            'host'            => 'localhost',
            'database'        => 'logs',
            'collection'      => 'jobs',
            'username'        => 'root',
            'password'        => 'admin',
            'port'            => 27017,
            'time_zone'       => 'UTC',
            'datetime_format' => 'Y-m-d H:i:s',
        ),
        'elasticsearch' => array (
            'host'      => 'localhost',
            'index'     => 'jobs',
            'type'      => 'logs',
            'username'  => 'admin',
            'password'  => 'es_admin',
            'port'      => 9200,
        )
    ),
);
