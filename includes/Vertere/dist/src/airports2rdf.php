#!/usr/bin/env php
<?php
ini_set('memory_limit', '2048M');
define('LIB_DIR', dirname(dirname(__FILE__)) . '/lib/');
define('MORIARTY_DIR', LIB_DIR.'moriarty/');
define('MORIARTY_ARC_DIR', LIB_DIR.'arc/');
include_once MORIARTY_DIR.'moriarty.inc.php';
include_once MORIARTY_DIR.'simplegraph.class.php';
include_once 'sequencegraph.class.php';

$graph = new SequenceGraph();
include_once 'namespaces.inc.php';

foreach ( array( 'Airport', 'Balloon_Port', 'Medium_Airport', 'Heliport', 'Large_Airport', 'Seaplane_Base', 'Small_Airport' ) as $type ) {
	$bag_uri = BASE_URI.strtolower($type).'s';
	$bags[NS_FLY.$type] = $bag_uri;
	$graph->add_bag_collection($bag_uri);
	$graph->add_resource_triple(NS_FLY.$type, NS_FLY.'airports', $bag_uri);
}

foreach ( array( 
	BASE_URI.'continents/africa',
	BASE_URI.'continents/asia',
	BASE_URI.'continents/europe',
	BASE_URI.'continents/north_america',
	BASE_URI.'continents/south_america',
	BASE_URI.'continents/oceania',
	BASE_URI.'continents/antarctica'
	) as $continent) {
	$graph->add_bag_collection($continent.'/airports');
	$graph->add_resource_triple($continent, NS_FLY.'airports', $continent.'/airports');
	foreach ( array( 'Airport', 'Balloon_Port', 'Medium_Airport', 'Heliport', 'Large_Airport', 'Seaplane_Base', 'Small_Airport' ) as $type ) {
		$typed_bag_part = strtolower($type).'s';
		$graph->add_bag_collection($continent.'/'.$typed_bag_part);
	}
}

echo $graph->to_ntriples();
$graph->remove_all_triples();

die();
while ($line = fgetcsv(STDIN))
{
	if (count($line) != 18) { die ("\n\n\nLine was not right:\n\n".print_r($line, true)); }
	foreach ( $line as $position => $column ) {
		$line[$position] = trim($column);
	}
	list(
		$id,				//a  0
		$ident,				//b  1  - done
		$type,				//c  2  - done
		$name,				//d  3  - done
		$latitude_deg,		//e  4  - done
		$longitude_deg,		//f  5  - done
		$elevation_ft,		//g  6 
		$continent,			//h  7  - done
		$iso_country,		//i  8
		$iso_region,		//j  9
		$municipality,		//k 10
		$scheduled_service,	//l 11
		$gps_code,			//m 12
		$iata_code,			//n 13
		$local_code,		//o 14
		$home_link,			//p 15
		$wikipedia_link,	//q 16 - done
		$keywords			//r 17
	) = $line;                 
	if ($id == "id") { continue; }
	$ident = strtoupper($ident);
	$airport = "${airport_bag}/${ident}";
	$ourairports = "http://www.ourairports.com/airports/${ident}/";
	$airport_bag = $bags[NS_FLY.'Airport'];
	$graph->add_resource_to_collection($airport_bag, $airport);

	$graph->add_resource_triple($airport, NS_RDF.'type', NS_FLY.'Airport');
	$graph->add_resource_triple($airport, NS_RDF.'type', NS_SCHEMA.'Airport');
	$graph->add_resource_triple($airport, NS_RDF.'type', NS_SCHEMA.'Place');
	$graph->add_resource_triple($airport, NS_FOAF.'primaryTopicOf', $ourairports);
	$graph->add_literal_triple($airport, NS_FLY.'icao_code', "${ident}");
	if (!empty($wikipedia_link)) {
		$wikipedia_key_fragment = str_replace('http://en.wikipedia.org/wiki/', '', $wikipedia_link);
		$dbpedia_uri = "http://dbpedia.org/resource/${wikipedia_key_fragment}";
		$graph->add_resource_triple($airport, NS_FOAF.'primaryTopicOf', $wikipedia_link);
		$graph->add_resource_triple($airport, NS_OWL.'sameAs', $dbpedia_uri);
	}
	
	$specific_type = $type == 'balloonport'    ? NS_FLY.'Balloon_Port'   : $specific_type;
	$specific_type = $type == 'heliport'       ? NS_FLY.'Heliport'       : $specific_type;
	$specific_type = $type == 'large_airport'  ? NS_FLY.'Large_Airport'  : $specific_type;
	$specific_type = $type == 'medium_airport' ? NS_FLY.'Medium_Airport' : $specific_type;
	$specific_type = $type == 'seaplane_base'  ? NS_FLY.'Seaplane_Base'  : $specific_type;
	$specific_type = $type == 'small_airport'  ? NS_FLY.'Small_Airport'  : $specific_type;

	$graph->add_resource_triple($airport, NS_RDF.'type', $specific_type);
	$graph->add_resource_to_collection($bags[$specific_type], $airport);
	
	if ($type == 'closed') { $graph->add_resource_triple($airport, NS_FLY.'status', NS_FLY.'Closed'); }
	
	if (!empty($name)) { $graph->add_literal_triple($airport, NS_FOAF.'name', $name, 'en-us'); }

	if (!empty($latitude_deg)) { $graph->add_literal_triple($airport, NS_GEO.'lat', $latitude_deg); }
	if (!empty($longitude_deg)) { $graph->add_literal_triple($airport, NS_GEO.'long', $longitude_deg); }
	if (!empty($latitude_deg) && !empty($longitude_deg)) { $graph->add_literal_triple($airport, NS_GRS.'point', "${latitude_deg} ${longitude_deg}"); }

	$continent = $continent == 'AF' ? BASE_URI.'continents/africa' : $continent;
	$continent = $continent == 'AS' ? BASE_URI.'continents/asia' : $continent;
	$continent = $continent == 'EU' ? BASE_URI.'continents/europe' : $continent;
	$continent = $continent == 'NA' ? BASE_URI.'continents/north_america' : $continent;
	$continent = $continent == 'SA' ? BASE_URI.'continents/south_america' : $continent;
	$continent = $continent == 'OC' ? BASE_URI.'continents/oceania' : $continent;
	$continent = $continent == 'AN' ? BASE_URI.'continents/antarctica' : $continent;
	$graph->add_resource_triple($airport, NS_GEOGRAPHIS.'onContinent', $continent);
	$graph->add_resource_to_collection($continent.'/airports', $airport);
	
	$country_uri = BASE_URI.'countries/'.$country;
	$graph->add_resource_triple($airport, NS_GEOGRAPHIS.'onContinent', $continent);
	
	// isoAlpha2

	echo $graph->to_ntriples();
	$graph->remove_all_triples();
}

//"id","ident","type","name","latitude_deg","longitude_deg","elevation_ft","continent","iso_country","iso_region","municipality","scheduled_service","gps_code","iata_code","local_code","home_link","wikipedia_link","keywords"

