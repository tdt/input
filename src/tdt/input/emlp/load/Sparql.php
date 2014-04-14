<?php

namespace Tdt\Input\EMLP\Load;

/**
 * The Sparql class loads triples into a triplestore.
 */
class Sparql extends ALoader
{


    private $buffer = array();
    private $graph;

    public function __construct($model, $command)
    {

        parent::__construct($model, $command);
    }

    public function init()
    {

        // Get the job and use the identifier as a graph name
        $job = $this->loader->job;

        // Create the graph name
        $graph_name = $this->loader->graph_name;

        $this->log("Initializing the Sparql loader, the graph that will be used is named $graph_name.");

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
    public function cleanUp()
    {

        $this->log("Cleaning up the Sparql loader, checking for remaining triples in the buffer.");

        try {

            // If the buffer isn't empty, load triples into the triple store
            while (!empty($this->buffer)) {

                $count = count($this->buffer) <= $this->loader->buffer_size ? count($this->buffer) : $this->loader->buffer_size;

                $this->log("Found $count remaining triples in the buffer, preparing them to load into the store.");

                $triples_to_send = array_slice($this->buffer, 0, $count);
                $this->addTriples($triples_to_send);

                $this->buffer = array_slice($this->buffer, $count);

                $count = count($this->buffer);
                $this->log("After the buffer was sliced, $count triples remained in the buffer.");
            }
        } catch (Exception $e) {
            $this->log("An error occured during the load of the triples. The message was: $e->getMessage().");
        }

        // Delete the older version(s) of this graph
        $this->deleteOldGraphs();

        // Save our new graph
        $this->graph->save();
    }

    /**
     * Perform the load.
     *
     * @param EasyRdf_Graph $chunk
     * @return void
     */
    public function execute(&$chunk)
    {

        if (!$chunk->isEmpty()) {

            // Don't use EasyRdf's ntriple serializer, as something might be wrong with its unicode byte characters
            // After serializing with semsol/arc and easyrdf, the output looks the same (with unicode characters), but after a
            // binary utf-8 conversion (see $this->serialize()) the outcome is very different, leaving easyrdf's encoding completely different
            // from the original utf-8 characters, and the semsol/arc encoding correct as the original.
            $ttl = $chunk->serialise('turtle');

            $arc_parser = \ARC2::getTurtleParser();

            $ser = \ARC2::getNTriplesSerializer();

            $arc_parser->parse('', $ttl);

            $triples = $ser->getSerializedTriples($arc_parser->getTriples());

            preg_match_all("/(<.*\.)/", $triples, $matches);

            if ($matches[0]) {
                $this->buffer = array_merge($this->buffer, $matches[0]);
            }

            $triple_count = count($matches[0]);
            $this->log("Added $triple_count triples to the load buffer.");

            while (count($this->buffer) >= $this->loader->buffer_size) {

                // Log the time it takes to load the triples into the store
                $start = microtime(true);
                $buffer_size = $this->loader->buffer_size;

                $triples_to_send = array_slice($this->buffer, 0, $buffer_size);

                $this->addTriples($triples_to_send);
                $this->buffer = array_slice($this->buffer, $buffer_size);

                $duration = round((microtime(true) - $start) * 1000, 2);
                $this->log("Took $buffer_size triples from the load buffer, loading them took $duration ms.");
            }
        }
    }

    /**
     * Insert triples into the triple store
     *
     * @param array $triples
     *
     * @return void
     */
    private function addTriples($triples)
    {

        $triples_string = implode(' ', $triples);

        $serialized = $this->serialize($triples_string);

        $query = $this->createInsertQuery($serialized);

        // If the insert fails, insert every triple one by one
        if (!$this->performQuery($query)) {

            $this->log("-------------------------------------- BAD TRIPLE DETECTED --------------------------------------");

            $this->log("Inserting triple by triple to avoid good triples not getting inserted because of the presence of a bad triple.");

            $totalTriples = count($triples);
            $this->log("Total triples to be inserted one by one is $totalTriples.");

            // Insert every triple one by one
            foreach ($triples as $triple) {

                $serialized = $this->serialize($triple);
                $query = $this->createInsertQuery($serialized);

                if (!$this->performQuery($query)) {
                    $this->log("Failed to insert the following triple: " . $triple, "error");
                } else {
                    $this->log("Succesfully inserted a triple to the triplestore.");
                }
            }

            $this->log("-------------------------------------------------------------------------------------------------");
        }
    }

    /**
     * Create an insert SPARQL query based on the graph id
     * @param string $triples (need to be serialized == properly encoded)
     *
     * @return string Insert query
     */
    private function createInsertQuery($triples)
    {

        $graph_id = $this->graph->graph_id;

        $query = "INSERT DATA INTO <$graph_id> {";
        $query .= $triples;
        $query .= ' }';

        return $query;
    }

    /**
     * Serialize triples to a format acceptable for a triplestore endpoint
     * @param string $triples
     *
     * @return string
     */
    private function serialize($triples)
    {

        $serialized_triples = preg_replace_callback(
            '/(?:\\\\u[0-9a-fA-Z]{4})+/',
            function ($v) {
                $v = strtr($v[0], array('\\u' => ''));
                return mb_convert_encoding(pack('H*', $v), 'UTF-8', 'UTF-16BE');
            },
            $triples
        );

        return $serialized_triples;
    }

    /**
     * Send a POST request to the triplestore endpoint using cURL
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return boolean
    */
    private function performQuery($query, $method = "GET")
    {

        if (!function_exists('curl_init')) {
            $this->log("cURL could not be retrieved as a command, make sure the CLI cURL is installed because it is necessary to perform the load. Aborting EML sequence.");
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
        );

        // Get curl handle and initiate the request
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);

        $response = curl_exec($ch);

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->log("After executing the insertion query the endpoint responded with code: $response_code");
        curl_close($ch);

        if ($response_code >= 400) {

            $this->log("The query failed with code " . $response_code);
            return false;
        } else {

            $this->log("The triples were succesfully inserted into the store.");
            return true;
        }
    }

    /**
     * Fetch the graph matching a graph_name as their identifier
     *
     * @return array List of graph names
     */
    private function fetchGraphs($graph_name)
    {
        $current_graph = $this->graph->graph_id;

        $query = "SELECT DISTINCT ?g WHERE { GRAPH ?g { ?s ?p ?o. filter regex(?g, \"$graph_name\", \"i\"). filter (?g != \"$current_graph\") } }";

        $this->log("Fetching all the graphs starting with the name: " . $this->graph->graph_name);

        $url = $this->loader->endpoint . "?query=" . urlencode($query) . "&format=" . urlencode('application/sparql-results+json');

        $defaults = array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => $this->loader->user . ":" . $this->loader->password,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
        );

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response_code <= 300 && $response) {

            $graphs = json_decode($response, true);
            $graphs = $graphs['results']['bindings'];

            $graph_names = array();
            foreach ($graphs as $graph_entry) {
                array_push($graph_names, $graph_entry['g']['value']);
            }

            return $graph_names;
        } else {
            $this->log("Something went wrong after querying the sparql-endpoint for all the graphs, the message we got is: " . $response, "error");
            return false;
        }
    }

    /**
     * Clear the old associated graphs with the given EML sequence based on the graph name.
     */
    private function deleteOldGraphs()
    {

        $graph_name = $this->graph->graph_name;

        $this->log("Before attaching the new graph, detaching and removing the old one, and possible zombie graphs");

        // List of graph names we have to delete in the triple store
        $graphs = $this->fetchGraphs($graph_name);

        if ($graphs) {

            foreach ($graphs as $graph_name) {

                if ($graph_name != $this->graph->graph_id) {

                    $query = "CLEAR GRAPH <$graph_name>";

                    $result = $this->performQuery($query);

                    // If all went ok, delete the graph entry
                    if ($result !== false) {

                        $this->log("The old version of the graph with id $graph_name has been deleted in the triple store.");
                    } else {
                        $this->log("The old version of the graph with id $graph_name was not deleted in the triple store.", "error");
                    }
                }
            }
        }

        // Delete the graph objects in our back-end
        $query_graph_name = "'". str_replace('/', '\/', $this->graph->graph_name) . "'";

        $graphs = \Graph::whereRaw("graph_name like $query_graph_name")->get();

        foreach ($graphs as $graph) {

            if ($graph->graph_id != $this->graph->graph_id) {

                $graph->delete();

                $this->log("The old version of the graph with id $graph->graph_id has been deleted in the back-end.");
            }
        }

        $this->log("--------------------------------------------------------------------------------------------------");
        $this->log("The new graph is identified by " . $this->graph->graph_id);
    }

    /**
     * Add a timestamp to the graph name so we can keep track of versions.
     * The graph is not removed untill the new graph is completely built up again.
     */
    private function addTimestamp($datetime)
    {

        $graph_id = $this->graph->graph_id;

        $query = "INSERT DATA INTO <" . $graph_id . "> {";
        $query .= "<" . $graph_id . "> <http://purl.org/dc/terms/created> \"$datetime\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .";
        $query .= ' }';

        if ($this->performQuery($query) !== false) {
            $this->log("Added the datetime ($datetime) meta-data to graph identified by " . $graph_id);
        } else {
            $this->log("Failed adding the datetime ($datetime) meta-data to graph identified by " . $graph_id);
        }
    }
}
