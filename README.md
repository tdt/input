Input
=====

tdt/input is a package which allows you to . It is an ET(M)L (Extract, Transform, Map and Load) tool written in PHP to get data in a triple graph store.



# Installation

## Using composer

add a require to your composer.json which requires tdt/input then simply perform:

'''bash
$ composer install
'''

You can then start using input by including the PSR-0 autoloader

'''php 
require 'vendor/autoinclude.php';
'''

## Without composer

Include all the classes and continue, but your should really start using composer. It's great for you. 


# Construct & config

## Example

'''php
// Extract Map and Load a CSV file to an ontology using a turtle file (you can find this file in examples directory)
$input = new Input(array(
         "source"    => "http://localhost/regions.csv",
         "extract"   => "CSV",
         "map"       => "RDF",
         "mapfile"   => "http://localhost/regions.csv.spec.ttl",
         "endpoint"  => "http://localhost:8890/sparql",
         "graph"     => "http://test.com/test"
         "load"      => "RDF",
         "delimiter" => ","
));

'''

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
 

