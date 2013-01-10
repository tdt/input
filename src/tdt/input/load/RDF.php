<?php

namespace tdt\input\load;

class RDF extends \tdt\input\ALoader {

    private $endpoint = 'http://157.193.213.125:8890/sparql';
    private $format = 'json';
    private $graph = 'http://mytest.com/regions';
    
    private $buffer_size = 1; //amount of chunks that are being inserted into one request
    private $buffer_index = 0;
    private $buffer = "";

    public function __construct($config) {
        parent::__construct($config);
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
        }

        $duration = microtime(true) - $start;
        echo "->Loading executed in $duration ms - buffer $this->buffer_index/$this->buffer_size \n";
    }

    private function query($triples) {
        $query = "INSERT IN GRAPH <$this->graph> { ";
        $query .= $triples;
        $query .= ' }';

        $response = json_decode($this->execSPARQL($query), true);

        if (!$response)
            $msg = "Query not inserted: $query";
        else
            $msg = $response['results']['bindings'][0]['callret-0']['value'] . '\n';
        
        echo $msg;
        
        \tdt\framework\Log::getInstance()->logInfo($msg);
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

        /*
          Here you find more options for curl:
          http://www.php.net/curl_setopt
         */

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

}
