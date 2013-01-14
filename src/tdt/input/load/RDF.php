<?php

namespace tdt\input\load;

class RDF extends \tdt\input\ALoader {

    private $endpoint;
    private $format;
    private $graph;
    private $buffer_size; //amount of chunks that are being inserted into one request
    //helper vars
    private $buffer = array();

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
            $config["buffer_size"] = 25;

        $this->buffer_size = $config["buffer_size"];
    }

    public function execute(&$chunk) {
        $start = microtime(true);

        if (!$chunk->is_empty()) {
            preg_match_all("/(<.*\.)/", $chunk->to_ntriples(), $matches);
            if ($matches[0])
                $this->buffer = array_merge($this->buffer, $matches[0]);


            if (count($this->buffer) >= $this->buffer_size) {
                $triples_to_send = array_slice($this->buffer, 0, $this->buffer_size);

                $serialized = preg_replace_callback('/(?:\\\\u[0-9a-fA-Z]{4})+/', function ($v) {
                                    $v = strtr($v[0], array('\\u' => ''));
                                    return mb_convert_encoding(pack('H*', $v), 'UTF-8', 'UTF-16BE');
                                }, implode(' ', $triples_to_send));

                $this->query($serialized);
                $this->buffer = array_slice($this->buffer, $this->buffer_size);
            }
        } else {
            echo "Empty chunk\n";
        }


        $duration = microtime(true) - $start;
        echo "  Loading executed in $duration s - " . count($this->buffer) . " triples left in buffer \n";
    }
    
    private function query($triples) {

        $query = "INSERT IN GRAPH <$this->graph> { ";
        $query .= $triples;
        $query .= ' }';

        $response = json_decode($this->execSPARQL($query), true);

        if ($response)
            echo "      " . $response['results']['bindings'][0]['callret-0']['value'] . "\n";
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

        if ($response_code != "200")
            echo "      Insert failed: " . $response_code . "\n" . $response;


        curl_close($ch);

        return $response;
    }

}
