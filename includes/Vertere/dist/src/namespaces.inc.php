<?php
define('BASE_URI', 'http://flybydata.com/');
$namespaces = array(
	'fly' => 'http://data.kasabi.com/dataset/airports/schema/',
	'dc' => 'http://purl.org/dc/elements/1.1/',
	'dcterms' => 'http://purl.org/dc/terms/',
	'foaf' => 'http://xmlns.com/foaf/0.1/',
	'owl' => 'http://www.w3.org/2002/07/owl#',
	'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
	'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
	'skos' => 'http://www.w3.org/2004/02/skos/core#',
	'xsd' => 'http://www.w3.org/2001/XMLSchema#',
	'schema' => 'http://schema.org/',
	'geo' => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
	'grs' => 'http://www.georss.org/georss/',
	'geographis' => 'http://www.telegraphis.net/ontology/geography/geography#'
);

foreach ($namespaces as $prefix => $uri)
{
	$graph->set_namespace_mapping($prefix, $uri);
	define('NS_'.str_replace('-', '_', strtoupper($prefix)), $uri);
}