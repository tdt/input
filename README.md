tdt/input is a package which allows you to set up your own ET(M)L (Extract, Transform, Map and Load) tool.

# Installation

## Using composer

add a require to your composer.json which requires tdt/input then simply perform:

```bash
$ composer install
```

You can then start using input by including the PSR-0 autoloader

```php 
require 'vendor/autoinclude.php';
```

# Construct & config

## Example config using Input

```php
// Extract Map and Load a CSV file to an ontology using a turtle file (you can find this file in examples directory)
$input = new Input(array(
   "name" => "Stations",
   "occurence" => 0,
   "extract" => array(
       "type" => "CSV",
       "delimiter" => ";",
       "source" => "http://data.irail.be/NMBS/Stations.csv"
    ),
    "map" => array(
       "type": "RDF",
       "mapfile": "http://demo.thedatatank.org/nmbs.ttl",
       "datatank_uri": "http://demo.thedatatank.org/",
       "datatank_package": "NMBS",
       "datatank_resource": "Stations"
    ),
    "load" => array(
      "type" => "CLI"
    )
));

```

## Example using the Scheduler to register the jobs

```php

//initialize the scheduler with a database config
$s = new Schedule(array(
         "name" => "dbname",
         "host" => "localhost",
         "user" => "username",
         "password" => "******"
   ));

//add a job with a certain occurence
$s->add(array(
   "name" => "Stations",
   "occurence" => 0,
   "extract" => array(
       "type" => "CSV",
       "delimiter" => ";",
       "source" => "http://data.irail.be/NMBS/Stations.csv"
    ),
    "map" => array(
       "type": "RDF",
       "mapfile": "http://demo.thedatatank.org/nmbs.ttl",
       "datatank_uri": "http://demo.thedatatank.org/",
       "datatank_package": "NMBS",
       "datatank_resource": "Stations"
    ),
    "load" => array(
      "type" => "CLI"
    )
));

//execute all jobs that are due in the queue (you need to execute this command using cronjobs)
$s->execute();

//if you want to delete a job, use this:
$s->delete("Stations");
```

## Configuration in tdt/start

Create a new project using composer:
```bash
composer create-project tdt/start
```

Alter composer.json and require:

```json
"tdt/input" : "dev-master"
```

Now update your project in order for input to be configured:

```bash
composer update
```

When you have configured tdt/start according to the documentation (filling out the configuration files), then you can also add the appropriate routes:

```json
{
        "namespace" : "tdt\\input",
        // Routes for this core
        "routes" : {
            "GET | TDTInput/Worker/?" : "scheduler\\controllers\\Worker",
            "GET | TDTInput/?(?P<format>\\.[a-zA-Z]+)?" : "scheduler\\controllers\\InputResourceController",
            "GET | TDTInput/(?P<resource>.*)\\.(?P<format>[a-zA-Z]+)" : "scheduler\\controllers\\InputResourceController",
            "GET | TDTInput/(?P<resource>.*?)(?P<test>/test)?" : "scheduler\\controllers\\InputResourceController",
            "PUT | TDTInput/(?P<resource>.*)" : "scheduler\\controllers\\InputResourceController",
            "POST | TDTInput/?" : "scheduler\\controllers\\InputResourceController",
            "DELETE | TDTInput/(?P<resource>.*)" : "scheduler\\controllers\\InputResourceController"

        }
}
```

Go to http://yourdomain.com/TDTInput

### API documenation when installed with tdt/start

You can find the API documentation at http://thedatatank.com

# Requirements

* Apache2
* php 5.3

Optional:

* A triple store, index or database

# License & copyright

© 2013 OKFN Belgium vzw/asbl

AGPLv3
 

