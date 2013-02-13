<?php

chdir("../");
set_include_path(get_include_path() . PATH_SEPARATOR . "../");
require_once("vendor/autoload.php");
use tdt\input\scheduler\Schedule;

$s = new Schedule(parse_ini_file("examples/custom/db.ini", false));
/*$s->add(array(
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
            ));*/
//$s->delete("test");
$s->execute();

