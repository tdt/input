<?php

namespace tdt\input\emlp\load;

class Sparql extends ALoader {


    private $buffer = array();
    private $graph_name;

    public function __construct($model) {

        parent::__construct($model);

        // Get the job and use the identifier as a graph name
        $job = $model->job;

        //Store graph in database TODO get configurable graph name?
        $this->graph_name = $job->collection_uri . '/' . $job->name;
        $this->log("Preparing the Sparql loader, the graph that will be used is named $this->graph_name.");
    }

    /**
     * After the loader has been called upon his last execute() method, triples might still remain in the buffer.
     * If so, load the remaining of them into the triple store.
     */
    public function cleanUp(){

        $this->log("Cleaning up the Sparql loader, checking for remaining triples in the buffer.");

        try{

            // If the buffer isn't empty, load triples into the triple store
            while(!empty($this->buffer)){

                $count = count($this->buffer) <= $this->loader->buffer_size ? count($this->buffer) : $this->loader->buffer_size;

                $this->log("Found $count remaining triples in the buffer, preparing them to load into the store.");

                $triples_to_send = array_slice($this->buffer, 0, $count);
                $this->addTriples(implode(' ', $triples_to_send));

                $this->buffer = array_slice($this->buffer, $count);

                $this->log("After the buffer was sliced, count($this->buffer) triples remained in the buffer.");
            }
        }catch(Exception $e){
            $this->log("An error occured during the load of the triples. The message was: $e->getMessage().");
        }

        // TODO just remove the graph with the same id (=graph_name)?
        $this->clearOldGraphs();
    }

    public function execute(&$chunk){

        // Log the time it takes to load the triples into the store
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

        $duration = (microtime(true) - $start) * 1000;
        $this->log("The loading process was executed in round($duration,3) ms.");

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

        if ($this->execSPARQL($query) !== false)
            $this->log("The triples were succesfully inserted into the store.");
        else
            $this->log("The triples were not successfully inserted into the store.");
    }

    /**
     * Send a POST requst using cURL
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return string
    */
    private function execSPARQL($query, $method = "POST") {

        if (!function_exists('curl_init')) {
            $this->log("cURL could not be retrieved as a command, make sure the CLI cURL is installed. Aborting the emlp sequence.");
            exit();
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

        // Get curl handle and initiate the request
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/plain"));

        $response = curl_exec($ch);

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->log("After executing the insertion query the endpoint responded with code: $response_code");
        curl_close($ch);

        if ($response_code >= 400) {
            $this->log("The query failed with code " . $response_code . " and response: " . $response);
        }

        return $response;
    }

    /**
     * TODO
     */
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
