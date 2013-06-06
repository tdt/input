<?php

namespace tdt\input\map;
use tdt\exceptions\TDTException;

//set_include_path(get_include_path() . PATH_SEPARATOR . "../vendor/tdt/input/");
define('VERTERE_DIR', __DIR__ . '/../../../../includes/Vertere/dist/');
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
            throw new TDTException(400,array("Map document not set in config"));

        if (!isset($config["datatank_uri"]))
            throw new TDTException(400,array("Destination datatank uri not set in config"));

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
            throw new TDTException(400,array("Mapping file location not correct\n"));
        }

        $spec = new \SimpleGraph();
        $spec->from_turtle($spec_file);

        //Find the spec in the graph
        $specs = $spec->get_subjects_of_type(NS_CONV . "Spec");

        if (count($specs) != 1) 
            throw new TDTException(400,array("Map document must contain exactly one conversion spec"));
        
        
        //Replace pseudo URIs
        $this->processURIParameters($spec, $config);

        $process_classpath = "examples/custom/process.class.php";

        //Check if mapping file is the current one
        //Load spec and create new Vertere converter
        $this->vertere = new \Vertere($spec, $specs[0], $process_classpath);
    }
    
    private function processURIParameters(&$spec, $config){
        //Override the uri placeholders in mapping file e.g., tdt:package:resource
        $subjects = $spec->get_subjects();
        $p = NS_CONV . "base_uri";
        
        $param_map = array("tdt" => "datatank_uri", "package" => "datatank_package", "resource"=>"datatank_resource");

        foreach ($subjects as $s) {
            if (!$spec->subject_has_property($s, $p))
                continue;

            //Get base URI
            $o = $spec->get_first_literal($s, array($p), "");

            $parts = explode(":", $o);

            //Does : give any results?
            if (empty($parts))
                continue;

            //If the first element is http or https, no find replace is performed
            if ($parts[0] == "http" || $parts[0] == "https")
                continue;
            
            //Strip part of URI after first slash 
            $last_part = $parts[count($parts) - 1];

            //is there someting after tdt:package:resource starting with a slash
            $pos = stripos($last_part, "/");
            
            if ($pos !== false) {
                $parts[count($parts) - 1] = substr($last_part, 0, $pos);
                $last_part = ltrim(substr($last_part, $pos), "/");
            } else
                $last_part = "";

            //Remove the triple to replace it
            $spec->remove_literal_triple($s, $p, $o);
            $spec_base_uri = "";

            foreach ($parts as $part) {
                /*switch ($part)
                case "joburi":
                    $spec_base_uri
                break;
                default:*/
                if (empty($part))
                    continue;
                
                if (!isset($param_map[$part]))
                    throw new TDTException(400,array("URI parameter $part is not valid."));
                
                if (!isset($config[$param_map[$part]]))
                    throw new TDTException(400,array("URI parameter $part was called from the mapping file, but not specified in config."));
                
                $spec_base_uri .= rtrim($config[$param_map[$part]], "/") . "/";
            }
            
            //Re-add modified triple
            $spec->add_literal_triple($s, $p, $spec_base_uri . $last_part);
        }
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
