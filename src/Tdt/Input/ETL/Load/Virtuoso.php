<?php

namespace Tdt\Input\ETL\Load;

use EasyRdf\GraphStore;
use EasyRdf\Serialiser\Ntriples;

/**
 * The Sparql class loads triples into a triplestore.
 */
class Virtuoso extends ALoader
{
    public function __construct($model, $command)
    {
        parent::__construct($model, $command);
    }

    public function init()
    {
        $this->deleteOldGraph();

        $this->bnode_replacements = [];
    }

    public function cleanUp()
    {
    }

    /**
     * Perform the load.
     *
     * @param EasyRdf_Graph $graph
     * @return void
     */
    public function execute($graph)
    {
        $ntriples_serialiser = new NTriples();

        $ntriples = $ntriples_serialiser->serialise($graph, 'ntriples');

        $this->addTriples($ntriples);
    }

    private function performQuery($query, $method = "GET")
    {
        $url = $this->loader->endpoint . "?query=" . urlencode($query);

        $defaults = array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => $this->loader->username . ":" . $this->loader->password,
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

        if ($response_code != 200) {
            $this->log("The query failed with code " . $response_code);

            curl_close($ch);
            return false;
        } else {
            $this->log("The triples were succesfully inserted into the store.");

            curl_close($ch);
            return true;
        }
    }

    private function deleteOldGraph()
    {
        $graph_name = $this->loader->graph;

        $this->log("Before attaching the new graph, detaching and removing the old one");


        $query = "CLEAR GRAPH <$graph_name>";
        $result = $this->performQuery($query);

        // If all went ok, delete the graph entry
        if ($result !== false) {
            $this->log("The old version of the graph with id $graph_name has been deleted in the triple store.");
        } else {
            $this->log("The old version of the graph with id $graph_name was not deleted in the triple store.", "error");
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
        $graph_name = $this->loader->graph;
        $query = "INSERT DATA INTO <$graph_name> {";
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
     * Insert triples into the triple store
     *
     * @param string $triples
     *
     * @return void
     */
    private function addTriples($triples)
    {
        preg_match_all('/(_:genid.*?)\s/is', $triples, $matches);

        foreach ($matches[0] as $match) {
            if (empty($this->bnode_replacements[$match])) {
                $bnode_replacement = '<http://bnode.org/' . str_random(10) . '>';

                $this->bnode_replacements[$match] = $bnode_replacement;
            }

            $bnode_uri = $this->bnode_replacements[$match];

            $triples = str_replace($match, $bnode_uri, $triples);
        }

        $triple_patterns = explode("\n", $triples);

        foreach ($triple_patterns as $pattern) {
            $serialized = $this->serialize($pattern);

            $query = $this->createInsertQuery($serialized);

            if (!$this->performQuery($query, 'POST')) {
                $this->log("This pattern was not succesfully loaded: " . $pattern, "eror");
            } else {
                $this->log("Succesfully added the pattern: " . $pattern);
            }
        }
    }
}
