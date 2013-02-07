<?php

namespace tdt\input\load;
use RedBean_Facade as R;

class RDF extends \tdt\input\ALoader {

    private $endpoint;
    private $format;
    private $graph;
    private $buffer_size; //amount of chunks that are being inserted into one request
    //helper vars
    private $buffer = array();

    public function __construct($config) {
    	if (!isset($config["system"]))
			throw new \Exception('Redbeans database not set in config');
		$connection = $config["system"] . ":host=" . $config["host"] . ";dbname=" . $config["name"];
		
		print($connection."\n");
		
		R::setup($config["system"] . ":host=" . $config["host"] . ";dbname=" . $config["name"], $config["user"], $config["password"]);
		
        if (!isset($config["endpoint"]))
            throw new \Exception('SPARQL endpoint not set in config');

        $this->endpoint = $config["endpoint"];

        if (!isset($config["format"]))
            $config["format"] = 'json';

        $this->format = $config["format"];

        if (!isset($config["graph"]))
            throw new \Exception('Destination graph not set in config');
		
		$date_time = R::isoDateTime();
		
		$graph_id =  $config["graph"] . "_" . hash('ripemd160',$date_time);
		
		$graph = R::dispense('graph');
		$graph->graph_name = $config["graph"];
		$graph->graph_id = $graph_id;
		$graph->version = $date_time;
		
		$id = R::store($graph);
		
		R::close();

        $this->graph = $graph_id;

        if (!isset($config["buffer_size"]))
            $config["buffer_size"] = 25;

        $this->buffer_size = $config["buffer_size"];
    }

    public function __destruct() {
        echo "Empty loader buffer...\n";

        while (!empty($this->buffer)) {
            $count = count($this->buffer) <= $this->buffer_size ? $this->buffer_size : count($this->buffer);
            
            $triples_to_send = array_slice($this->buffer, 0, $count);

            $this->query(implode(' ', $triples_to_send));
            $this->buffer = array_slice($this->buffer, $count);
        }
    }

    public function execute(&$chunk) {
        $start = microtime(true);

        if (!$chunk->is_empty()) {
            preg_match_all("/(<.*\.)/", $chunk->to_ntriples(), $matches);
            if ($matches[0])
                $this->buffer = array_merge($this->buffer, $matches[0]);


            if (count($this->buffer) >= $this->buffer_size) {
                $triples_to_send = array_slice($this->buffer, 0, $this->buffer_size);

                $this->query(implode(' ', $triples_to_send));
                
                $this->buffer = array_slice($this->buffer, $this->buffer_size);
            }
        } else {
            echo "Empty chunk\n";
        }


        $duration = (microtime(true) - $start) * 1000;
        echo "|_Loading executed in $duration ms - " . count($this->buffer) . " triples left in buffer \n";
    }

    private function query($triples) {
        $serialized = preg_replace_callback('/(?:\\\\u[0-9a-fA-Z]{4})+/', function ($v) {
                        $v = strtr($v[0], array('\\u' => ''));
                        return mb_convert_encoding(pack('H*', $v), 'UTF-8', 'UTF-16BE');
                    }, $triples);
        
        $query = "INSERT IN GRAPH <$this->graph> { ";
        $query .= $serialized;
        $query .= ' }';
        
        echo " |_Flush buffer... ";

        $response = json_decode($this->execSPARQL($query), true);

        if ($response)
            echo $response['results']['bindings'][0]['callret-0']['value'] . "\n";
    }

    private function execSPARQL($query) {

        // is curl installed?
        if (!function_exists('curl_init')) {
            throw new \Exception('CURL is not installed!');
        }

        // get curl handle
        $ch = curl_init();

        // set request url
        curl_setopt($ch, CURLOPT_URL, $this->endpoint
                . '?query=' . urlencode($query)
                . '&format=' . $this->format);

        // return response, don't print/echo
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        $response = curl_exec($ch);
        
        if (!$response)
            echo "endpoint returned error: " . curl_error($ch) . " - ";
        
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response_code != "200")
            echo "query failed: " . $response_code . "\n" . $response;


        curl_close($ch);

        return $response;
    }

}
