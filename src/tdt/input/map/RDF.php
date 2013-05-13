<?php

namespace tdt\input\map;

set_include_path(get_include_path() . PATH_SEPARATOR . "../vendor/tdt/input/");
define('VERTERE_DIR', 'includes/Vertere/dist/');
define('MORIARTY_DIR', VERTERE_DIR . 'lib/moriarty/');
define('MORIARTY_ARC_DIR', VERTERE_DIR . 'lib/arc/');

define('NS_CONV', 'http://example.com/schema/data_conversion#');
define('NS_RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');


include_once MORIARTY_DIR . 'moriarty.inc.php';
include_once MORIARTY_DIR . 'simplegraph.class.php';
include_once VERTERE_DIR . 'inc/sequencegraph.class.php';
include_once VERTERE_DIR . 'inc/vertere.class.php';
include_once VERTERE_DIR . 'inc/diagnostics.php';

class RDF extends \tdt\input\AMapper {

    private $vertere;
    public $log;
    
    function __construct($config, &$log) {
        $this->log = &$log;
        
        if (!isset($config["mapfile"]))
            throw new \Exception("Map document not set in config");

        if (!isset($config["datatank_uri"]))
            throw new \Exception('Destination datatank uri not set in config');

        $ch = curl_init();
        $timeout = 5; // set to zero for no timeout
        curl_setopt($ch, CURLOPT_URL, $config['mapfile']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        if (!$spec_file = curl_exec($ch)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        if (empty($spec_file)) {
            throw new \Exception("Mapping file location not correct\n");
        }


        $spec = new \SimpleGraph();
        $spec->from_turtle($spec_file);

        //Find the spec in the graph
        $specs = $spec->get_subjects_of_type(NS_CONV . "Spec");

        if (count($specs) != 1) {
            throw new \Exception("Map document must contain exactly one conversion spec");
        }

        if (!isset($config["datatank_package"]) || !isset($config["datatank_resource"]))
            throw new \Exception('Destination datatank package or resource not set in config. A Datatank will not be used as base URI in the RDF output, this prevents automatically URI resolvement');
        else {
            $this->base_uri = $config["datatank_uri"] . $config["datatank_package"] . "/" . $config["datatank_resource"] . "/";

            //Override 
            $subjects = $spec->get_subjects();
            $p = NS_CONV . "base_uri";

            $spec_base_uri = $spec->get_first_literal($specs[0], array($p), "");

            foreach ($subjects as $s) {
                if (!$spec->subject_has_property($s, $p))
                    continue;

                //Get base URI
                $o = $spec->get_first_literal($s, array($p), "");

                //Remove the triple to replace it
                $spec->remove_literal_triple($s, $p, $o);

                //Replace the base URI's from the mapping file with the datatank URI
                $o = str_replace($spec_base_uri, $this->base_uri, $o);

                //Re-add modified triple
                $spec->add_literal_triple($s, $p, $o);
            }
        }
        $process_classpath = "examples/custom/process.class.php";

        //Check if mapping file is the current one
        //Load spec and create new Vertere converter
        $this->vertere = new \Vertere($spec, $specs[0], $process_classpath);
    }

    public function execute(&$chunk) {
        $start = microtime(true);
        //Apply mapping to chunk
        $graph = $this->vertere->convert_array_to_graph($chunk);
        
        $duration = (microtime(true) - $start) * 1000;
        $this->log[] = "Mapping executed in $duration ms";

        return $graph;
    }

}
