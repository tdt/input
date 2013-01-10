#!/usr/bin/env php
<?php
ini_set('memory_limit', '2048M');
define('LIB_DIR', dirname(__FILE__) . '/lib/');
define('MORIARTY_DIR', LIB_DIR.'moriarty/');
define('MORIARTY_ARC_DIR', LIB_DIR.'arc/');
include_once MORIARTY_DIR.'moriarty.inc.php';
include_once MORIARTY_DIR.'simplegraph.class.php';

$graph = new SimpleGraph();
$previous_subject = null;

while ($line = fgets(STDIN))
{
	$line = trim($line);
	if (empty($line)) { continue; }

	$matched_as_triple = preg_match('%^<([^>]*)>[ \t]<([^>]*)> (.*)$%', $line, $matches);
	if (!$matched_as_triple) {
		die("The following line does not appear to be a triple:\n${line}\n\n");
	}

	$subject = $matches[1];
	$property = $matches[2];
	$remainder = $matches[3];

	if ($subject != $previous_subject && $previous_subject != null) {
		echo $graph->to_ntriples();
		$graph->remove_all_triples();
	}
	
	$graph->add_turtle($line);
	$previous_subject = $subject;
}
echo $graph->to_ntriples();
