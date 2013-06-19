<?php

namespace tdt\input\load;

use RedBean_Facade as R;
use tdt\exceptions\TDTException;
use tdt\uri\RequestURI;

class RDF extends \tdt\input\ALoader {

    private $endpoint;
    private $format;
    private $buffer_size; //amount of chunks that are being inserted into one request
//helper vars
    private $buffer = array();
    private $old_graphs;
    private $graph, $graph_name;
    public $log;

    /**
     * Validation already done earlier
     */
    public function __construct($config, &$log) {
        $this->log = &$log;
        if (!isset($config["endpoint"]))
            throw new TDTException(400,array('SPARQL endpoint not set in config'));
        $this->endpoint = $config["endpoint"];
        $this->format = "json";


        if (!isset($config["datatank_uri"]))
            throw new TDTException(400,array('Destination datatank uri not set in config'));

        $this->datatank_uri = $config["datatank_uri"];

        if (!isset($config["datatank_package"]))
            throw new TDTException(400,array('Destination datatank package not set in config'));

        $this->datatank_package = $config["datatank_package"];

        if (!isset($config["datatank_resource"]))
            throw new TDTException(400,array('Destination datatank resource not set in config'));

        $this->datatank_resource = $config["datatank_resource"];

        if (!isset($config["datatank_user"])) {
            //echo 'User for datatank not set in config\n';
            $this->datatank_user = "";
        } else {
            $this->datatank_user = $config["datatank_user"];
        }

        if (!isset($config["datatank_password"])) {
            //echo 'Password for datatank not set in config\n';
            $this->datatank_password = "";
        } else {
            $this->datatank_password = $config["datatank_password"];
        }

        if (!isset($config["endpoint_user"])) {
            //echo "User for endpoint not set in config\n";
            $this->endpoint_user = "";
        } else {
            $this->endpoint_user = $config["endpoint_user"];
        }

        if (!isset($config["endpoint_password"])) {
            //echo "Password for endpoint not set in config\n";
            $this->endpoint_password = "";
        } else {
            $this->endpoint_password = $config["endpoint_password"];
        }

        //Store graph in database with the name of the job as identifier!
        // This means that we need to strip the uri until the part right next to tdtinput  
        // The uri we're getting is in the form of http(s)://.../tdtinput/name/run(test,...)
        $uri = new RequestURI();
        $uri = $uri->getURI();
        $pos = strrpos($uri,"/");
        $uri = substr($uri, 0, $pos);
        
        $this->graph_name = $uri;

        $time = time();
        $date_time = date("c", $time);

        $graph_id = $this->graph_name . "#" . $time;

        $graph = R::dispense('graph');
        $graph->graph_name = $this->graph_name;
        $graph->graph_id = $graph_id;
        $graph->version = $date_time;

        $this->old_graphs = $this->getAllGraphs($this->graph_name);

        R::store($graph);
        R::close();

        $this->graph = $graph_id;

        $this->addTimestamp($date_time);
        
        if (!isset($config["buffer_size"]))
            $config["buffer_size"] = 4;

        $this->buffer_size = $config["buffer_size"];
    }

    public function cleanUp() {
        $this->log[] = "Empty loader buffer";
        try {
            while (!empty($this->buffer)) {
                $count = count($this->buffer) <= $this->buffer_size ? count($this->buffer) : $this->buffer_size;

                $triples_to_send = array_slice($this->buffer, 0, $count);
                $this->addTriples(implode(' ', $triples_to_send));

                $this->buffer = array_slice($this->buffer, $count);
            }
        } catch (\Exception $e) {
            throw new TDTException(500, array("ETML Failed: " . $e->getMessage()));
        }

        $this->log[] = "Inserting resource into your datatank.";
        //Add SPARQL resource with describe query to datatank
        $data = array(
            "resource_type" => "generic",
            "generic_type" => "ld",
            "endpoint" => $this->endpoint,
            "documentation" => "Linked Data resource inserted by tdt/input for the retrieval of URIs in $this->datatank_package/$this->datatank_resource"
        );

        if (isset($this->endpoint_user))
            $data["endpoint_user"] = $this->endpoint_user;

        if (isset($this->endpoint_password))
            $data["endpoint_password"] = $this->endpoint_password;

        //Build PUT uri for datatank
        $uri = $this->datatank_uri . "TDTAdmin/Resources/$this->datatank_package/$this->datatank_resource";

        $ch = curl_init($uri);

        curl_setopt($ch, CURLOPT_USERPWD, $this->datatank_user . ":" . $this->datatank_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response_code >= 400) {
            $this->log[] = "PUT request to The DataTank instance for package: $this->datatank_package and resource: $this->datatank_resource failed!";
            $this->log[] = "Response code given was: " . $response_code;
        } else {
            $this->log[] = "Request to add resource in The DataTank succeeded with code " . $response_code;
            $this->log[] = "Resources available under " . $this->datatank_uri . "$this->datatank_package/$this->datatank_resource";
        }
        $this->clearOldGraphs();
    }

    public function execute(&$chunk) {
        $start = microtime(true);

        if (!$chunk->is_empty()) {
            preg_match_all("/(<.*\.)/", $chunk->to_ntriples(), $matches);
            if ($matches[0])
                $this->buffer = array_merge($this->buffer, $matches[0]);   

            while (count($this->buffer) >= $this->buffer_size) {
                $triples_to_send = array_slice($this->buffer, 0, $this->buffer_size);                
                $this->addTriples(implode(' ', $triples_to_send));
                $this->buffer = array_slice($this->buffer, $this->buffer_size);
            }
        }

        $duration = (microtime(true) - $start) * 1000;
        $this->log[] = "Loading executed in $duration ms - " . count($this->buffer) . " triples left in buffer";
    }

    private function clearOldGraphs() {
        $this->log[] = "deleting: " . print_r($this->old_graphs, true);
        foreach ($this->old_graphs as $graph) {
            $graph_id = $graph["graph_id"];
            $query = "CLEAR GRAPH <$graph_id>";

            $result = $this->execSPARQL($query);

            if ($result  !== false) {
                $response = json_decode($result, true);

                //if ($response)
                //    $this->log[] = print_r($response['results'], true);
                
                $this->deleteGraph($graph_id);

                $this->log[] = "Old version of graph $graph is cleared!";
            } else {
                $this->log["errors"][] = "Old version of graph $graph was not cleared!";
            }
        }
    }

    private function addTimestamp($datetime) {
        $query = "INSERT DATA INTO <" . $this->graph . "> {";
        $query .= "<" . $this->graph . "> <http://purl.org/dc/terms/created> \"$datetime\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .";
        $query .= ' }';

        if ($this->execSPARQL($query) !== false)
            $this->log[] = "Graph " . $this->graph . " added on $datetime. Metadata added!";
        else
            $this->log["errors"][] = "Graph " . $this->graph . " added on $datetime, but the metadata was not added!";
        //throw new \tdt\framework\TDTException(500,array("Triples were not inserted!"));
    }

    private function addTriples($triples) {
        $serialized = preg_replace_callback('/(?:\\\\u[0-9a-fA-Z]{4})+/', function ($v) {
                    $v = strtr($v[0], array('\\u' => ''));
                    return mb_convert_encoding(pack('H*', $v), 'UTF-8', 'UTF-16BE');
                }, $triples);

        $query = "INSERT DATA INTO <$this->graph> {";
        $query .= $serialized;
        $query .= ' }';


        $this->log[] = "Flush buffer... ";

        if ($this->execSPARQL($query) !== false)
            $this->log[] = "Triples inserted in  $this->graph_name !";
        else
            $this->log["errors"][] = "Triples were not inserted!";
        //throw new \tdt\framework\TDTException("Triples were not inserted!");
    }

    /**
     * Send a POST requst using cURL 
     * @param string $url to request 
     * @param array $post values to send 
     * @param array $options for cURL 
     * @return string 
     */
    private function execSPARQL($query, $method = "POST") {
// is curl installed?
        if (!function_exists('curl_init')) {
            throw new \Exception('CURL is not installed!');
        }

        $post = array(
            "update" => $query
        );

        $url = $this->endpoint . "?query=" . urlencode($query);

        $defaults = array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => $this->endpoint_user . ":" . $this->endpoint_password,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_POSTFIELDS => http_build_query($post)
//CURLOPT_PFIELDS => http_build_query($query)
        );

// get curl handle
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/plain"));

        $response = curl_exec($ch);

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->log[] = "Endpoint returned: $response_code";
        curl_close($ch);
        
        if ($response_code >= 400) {
            $this->log["errors"][] = "Query failed: " . $response_code . ": " . $response;
            return false;
        }

        return $response;
    }

    private function getAllGraphs($graph_name) {
        return R::getAll(
                        "SELECT graph_id
            FROM graph WHERE graph_name = :graph_name", array(":graph_name" => $graph_name)
        );
    }

    private function deleteGraph($graph_id) {
        return R::exec(
                        "DELETE FROM graph WHERE graph_id=:graph_id;", array(":graph_id" => $graph_id));
    }

}

