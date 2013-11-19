<?php

namespace tdt\input\emlp\load;

class Sparql extends ALoader {


    private $buffer = array();
    private $graph;

    public function __construct($model) {

        parent::__construct($model);

        // Get the job and use the identifier as a graph name
        $job = $model->job;

        // Create the graph name
        $graph_name = $model->graph_name;
        $this->log("Preparing the Sparql loader, the graph that will be used is named $graph_name.");

        // Store the graph to counter dirty reads
        $time = time();

        $graph = new \Graph();
        $graph->graph_name = $graph_name;
        $graph->graph_id = $graph_name . '#' . $time;
        $graph->version = date('c', $time);

        $this->graph = $graph;
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

                $count = count($this->buffer);
                $this->log("After the buffer was sliced, $count triples remained in the buffer.");
            }
        }catch(Exception $e){
            $this->log("An error occured during the load of the triples. The message was: $e->getMessage().");
        }

        // Delete the older version(s) of this graph
        $this->deleteOldGraphs();

        // Save our new graph
        $this->graph->save();
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

        $duration = round((microtime(true) - $start) * 1000, 3);
        $this->log("The loading process was executed in $duration ms.");

    }

    private function addTriples($triples) {

        $serialized = preg_replace_callback('/(?:\\\\u[0-9a-fA-Z]{4})+/', function ($v) {
                                                $v = strtr($v[0], array('\\u' => ''));
                                                return mb_convert_encoding(pack('H*', $v), 'UTF-8', 'UTF-16BE');
                                            },
                                            $triples);

        $graph_id = $this->graph->graph_id;
        $query = "INSERT DATA INTO <$graph_id> {";
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
            $this->log("The executed query that failed was the following: " . $query);
        }

        return $response;
    }

    /**
     * Clear the old associated graphs with the given eml sequence
     */
    private function deleteOldGraphs() {

        $graph_name = $this->graph->graph_name;
        $this->log("Removing the old graphs identified by the name $graph_name.");

        // Replace all forward slashes in the graph_name with escaped ones
        $query_graph_name = "'". str_replace('/', '\/', $graph_name) . "'";
        $graphs = \Graph::whereRaw("graph_name like $query_graph_name")->get();

        foreach ($graphs as $graph) {

            $query = "CLEAR GRAPH <$graph->graph_id>";

            $result = $this->execSPARQL($query);

            // If all went ok, delete the graph entry
            if($result !== false){

                $response = json_decode($result, true);
                $graph->delete();
                $this->log("The old version of the graph with id $graph->graph_id has been deleted.");
            }else{
                $this->log("The old version of the graph with id $graph->graph_id was not deleted.");
            }
        }
    }

    /**
     * Add a timestamp to the graph name so we can keep track of versions.
     * The graph is not removed untill the new graph is completely built up again.
     */
    private function addTimestamp($datetime){

        $graph_id = $this->graph->graph_id;

        $query = "INSERT DATA INTO <" . $graph_id . "> {";
        $query .= "<" . $graph_id . "> <http://purl.org/dc/terms/created> \"$datetime\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .";
        $query .= ' }';

        if ($this->execSPARQL($query) !== false)
            $this->log("Added the datetime ($datetime) meta-data to graph identified by " . $graph_id);
        else
            $this->log("Failed adding the datetime ($datetime) meta-data to graph identified by " . $graph_id);
    }
}
