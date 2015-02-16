# Input package

[![Latest Stable Version](https://poser.pugx.org/tdt/input/version.png)](https://packagist.org/packages/tdt/input)
[![Build Status](https://travis-ci.org/tdt/input.png?branch=development)](https://travis-ci.org/tdt/input)

This branch serves the specific purpose of loading graph results from a SPARQL query into a MongoDB.

# Configuration

## Dependencies
This package expects [the MongoDB package for Laravel](https://github.com/jenssegers/laravel-mongodb) is installed and properly added through the service providers:

'Jenssegers\Mongodb\MongodbServiceProvider',
'Tdt\Input\InputServiceProvider'

## Database
Next add the mongodb entry to your database.php file:

    'mongodb' => array(
        'driver'   => 'mongodb',
        'host'     => 'localhost',
        'port'     =>  27017,
        'username' => 'root',
        'password' => '',
        'database' => 'database'
    ),

# Deploy

Publish the assets so that you can use the UI to create a new Job.

# Usage

Once a new job is configured you can use it through the command input:execute as follows:

    > php artisan input:execute {identifier of the job}




