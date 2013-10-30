<?php

namespace tdt\input\emlp\load;

class Sparql extends ALoader {


    private $buffer = array();
    private $graph_name;

    public function __construct($model) {

        parent::__construct($model);

        // Get the job and use the identifier as a graph name
        $job = $model->job;

        /*$this->log = &$log;
        if (!isset($config["endpoint"]))
            throw new TDTException(400,array('SPARQL endpoint not set in config'));
        $this->loader->endpoint = $config["endpoint"];
        $this->format = "json";*/

        //Store graph in database TODO get configurable graph name?
        $this->graph_name = $job->collection_uri . '/' . $job->name;

        //$time = time();
        //$date_time = date("c", $time);

        // Is this still necessary?
        //$graph_id = $this->graph_name . "#" . $time;
        /*
        $graph = R::dispense('graph');
        $graph->graph_name = $this->graph_name;
        $graph->graph_id = $graph_id;
        $graph->version = $date_time;

        //$this->old_graphs = $this->getAllGraphs($this->graph_name);

        R::store($graph);
        R::close();

        $this->graph = $graph_id;

        $this->addTimestamp($date_time);

        if (!isset($config["buffer_size"]))
            $config["buffer_size"] = 4;

        $this->loader->buffer_size = $config["buffer_size"];*/
    }

    public function cleanUp() {

        //$this->log[] = "Empty loader buffer";

        try{
            while (!empty($this->buffer)) {

                $count = count($this->buffer) <= $this->loader->buffer_size ? count($this->buffer) : $this->loader->buffer_size;

                $triples_to_send = array_slice($this->buffer, 0, $count);
                $this->addTriples(implode(' ', $triples_to_send));

                $this->buffer = array_slice($this->buffer, $count);
            }
        }catch(Exception $e){
            echo "We failed something in loader.";
        }

        //$this->log[] = "Inserting resource into your datatank.";
        $this->clearOldGraphs();
    }

    public function execute(&$chunk){

        $start = microtime(true);

        if (!$chunk->isEmpty()) {
            preg_match_all("/(<.*\.)/", $chunk->serialise('ntriples'), $matches);

            if($matches[0])
                $this->buffer = array_merge($this->buffer, $matches[0]);

            while(count($this->buffer) >= $this->loader->buffer_size) {
                $triples_to_send = array_slice($this->buffer, 0, $this->loader->buffer_size);
                $this->addTriples(implode(' ', $triples_to_send));
                $this->buffer = array_slice($this->buffer, $this->loader->buffer_size);
            }
        }

        //$duration = (microtime(true) - $start) * 1000;
        //$this->log[] = "Loading executed in $duration ms - " . count($this->buffer) . " triples left in buffer";

    }

    private function addTriples($triples) {

        $serialized = preg_replace_callback('/(?:\\\\u[0-9a-fA-Z]{4})+/', function ($v) {
                                                $v = strtr($v[0], array('\\u' => ''));
                                                return mb_convert_encoding(pack('H*', $v), 'UTF-8', 'UTF-16BE');
                                            },
                                            $triples);

        $query = "INSERT DATA INTO <$this->graph_name> {";
        $query .= $serialized;
        $query .= ' }';


       //$this->log[] = "Flush buffer... ";

        if ($this->execSPARQL($query) !== false)
            echo "triples were inserted";

        else
            echo "failed to insert triples";
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

        $url = $this->loader->endpoint . "?query=" . urlencode($query);

        $defaults = array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => $this->loader->user . ":" . $this->loader->password,
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

        echo "Endpoint returned: $response_code";
        curl_close($ch);

        if ($response_code >= 400) {
            //$this->log["errors"][] = "Query failed: " . $response_code . ": " . $response;
            return false;
        }

        return $response;
    }

    private function clearOldGraphs() {

        /*$this->log[] = "deleting: " . print_r($this->old_graphs, true);
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
        }*/
    }
/*
    private function addTimestamp($datetime) {
        $query = "INSERT DATA INTO <" . $this->graph . "> {";
        $query .= "<" . $this->graph . "> <http://purl.org/dc/terms/created> \"$datetime\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .";
        $query .= ' }';

        if ($this->execSPARQL($query) !== false)
            $this->log[] = "Graph " . $this->graph . " added on $datetime. Metadata added!";
        else
            $this->log["errors"][] = "Graph " . $this->graph . " added on $datetime, but the metadata was not added!";
        //throw new \tdt\framework\TDTException(500,array("Triples were not inserted!"));
    }*/
}
