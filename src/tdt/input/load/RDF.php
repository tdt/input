<?php

namespace tdt\input\load;

class RDF extends \tdt\input\ALoader {

    private $endpoint;
    private $format;
    private $graph;
    private $buffer_size; //amount of chunks that are being inserted into one request
    //helper vars
    private $buffer_index = 0;
    private $buffer = "";

    public function __construct($config) {

        if (!isset($config["endpoint"]))
            throw new \Exception('SPARQL endpoint not set in config');

        $this->endpoint = $config["endpoint"];

        if (!isset($config["format"]))
            $config["format"] = 'json';
        
        $this->format = $config["format"];

        if (!isset($config["graph"]))
            throw new \Exception('Destination graph not set in config');
        
        $this->graph = $config["graph"];

        if (!isset($config["buffer_size"]))
            $config["buffer_size"] = 1;
            
        $this->buffer_size = $config["buffer_size"];
    }

    public function execute(&$chunk) {
        $start = microtime(true);
        
        if (!$chunk->is_empty()) {
            $this->buffer .= $chunk->to_ntriples();
            $this->buffer_index += 1;

            if ($this->buffer_size <= $this->buffer_index) {
                $this->query($this->buffer);
                $this->buffer = "";
                $this->buffer_index = 0;
            }
        }else{
            echo "Empty chunk\n";
        }
        

        $duration = microtime(true) - $start;
        echo "->Loading executed in $duration ms - buffer $this->buffer_index/$this->buffer_size \n ";
    }

    private function query($triples) {
        
        $query = "INSERT IN GRAPH <$this->graph> { ";
        $query .= $triples;
        $query .= ' }';

        $response = json_decode($this->execSPARQL($query), true);

        if ($response)
            echo $response['results']['bindings'][0]['callret-0']['value'] . "\n";
    }

    private function execSPARQL($query) {

        // is curl installed?
        if (!function_exists('curl_init')) {
            die('CURL is not installed!');
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
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ( $response_code != "200")
            echo "Insert failed: " . $response_code  . "\n";
        

        curl_close($ch);

        return $response;
    }

}
