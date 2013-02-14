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

## Without composer

Include all the classes and continue, but your should really start using composer. It's great for you. 

# Construct & config

## Example config using Input

```php
// Extract Map and Load a CSV file to an ontology using a turtle file (you can find this file in examples directory)
$input = new Input(array(
         "source"    => "http://localhost/regions.csv",
         "extract"   => "CSV",
         "map"       => "RDF",
         "mapfile"   => "http://localhost/regions.csv.spec.ttl",
         "endpoint"  => "http://localhost:8890/sparql",
         "graph"     => "http://test.com/test"
         "load"      => "RDF",
         "delimiter" => ",",
         "name" => "dbname",
         "host" => "localhost",
         "user" => "username",
         "password" => "******",

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
            "name" => "test",
            "occurence" => 60,
            "config" => array(
                "source" => "http://data.irail.be/NMBS/Stations.xml",
                "extract" => "XML",
                "map" => "RDF",
                "mapfile" => "http://localhost/nmbsstations.csv.spec.ttl",
                "load" => "RDF",
                "arraylevel" => 2,
                "endpoint" => "http://localhost:8890/sparql",
                "graph" => "http://example.com/test"
            )
       ));

//execute all jobs that are due in the queue (you need to execute this command using cronjobs)
$s->execute();

//if you want to delete a job, use this:
$s->delete("test");
```


## Specific configuration options

### Extractors

To use an extractor, you will need to specify a source indicating the URL of a file, and an "extract" option defined with an extractor name:

#### CSV

Extra parameters:

* delimiter: the delimiter in the CSV file

#### XML

Extra parameters:

* arraylevel: an integer indicating how deep we have to look for an array

### Transformers

Not yet implemented

### Mappers

#### RDF

The only mapper right now: map to an ontology using a turtle file.

Extra parameters:

* mapfile: a URI to a mapping file

### Loaders

#### CLI

A very easy loader: outputs everything to standard output using var_dumps. No extra parameters.

#### RDF

Load data in a triple store. Therefore we will need a triplestore (such as 4-store, virtuoso, sesame, etc).

Extra parameters:

* endpoint: the URI to your sparql endpoint
* graph: the name of your new graph


# Requirements

* Apache2
* php 5.3

Optional:

* A triple store, index or database

# License & copyright

© 2013 OKFN Belgium vzw/asbl

AGPLv3
 

