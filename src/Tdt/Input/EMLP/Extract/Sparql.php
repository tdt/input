<?php

namespace Tdt\Input\EMLP\Extract;

/**
 * This extractor reads data from a sparql endpoint
 * and returns it as an instance of an \EasyRdf_Graph
 */

class Sparql extends AExtractor
{
    private static $PAGE_SIZE = 50;

    protected function open()
    {
        // Count all the triples that are fetched by the sparql query

        $this->query = trim($this->extractor->query);
        $this->endpoint_user = trim($this->extractor->user);
        $this->endpoint_password = trim($this->extractor->password);
        $this->endpoint = trim($this->extractor->endpoint);

        // Derive the count query from the given SPARQL query
        $count = $this->performCountQuery($this->query, $this->endpoint, $this->endpoint_user, $this->endpoint_password);

        $this->total_count = $count;
        $this->page_count = 0;

        // Fill the graph to return
        $this->buffered_graph = $this->executeQuery($this->query, $this->endpoint, $this->endpoint_user, $this->endpoint_password);

        $this->page_count++;
    }

    /**
     * Tells us if there are more chunks to retrieve
     *
     * @return bool
     */
    public function hasNext()
    {
        return !empty($this->buffered_graph) && $this->buffered_graph->countTriples() > 0;
    }

    /**
     * Gives us the next chunk to process through our ETML
     *
     * @return \EasyRdf_Graph
     */
    public function pop()
    {
        $graph = $this->buffered_graph;

        // Fetch the next page of the SPARQL query
        $this->buffered_graph = $this->executeQuery($this->query, $this->endpoint, $this->endpoint_user, $this->endpoint_password);

        $this->page_count++;

        return $graph;
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     *
     * @return void
     */
    protected function close()
    {
    }

    /**
     * Create the sparql query and return the results
     *
     * @param $query    string The SPARQL query
     * @param $endpoint string The endpoint to send the query to
     * @param $user     string The user that has read permissions on the endpoint
     * @param $password string The password of the user
     *
     * @return \EasyRdf_Graph
     */
    private function executeQuery($query, $endpoint, $user, $password)
    {
        // Calculate the limit and offset
        $offset = $this->page_count * self::$PAGE_SIZE;
        $limit = self::$PAGE_SIZE;

        $query .= " OFFSET $offset LIMIT $limit";

        $query = str_replace('%23', '#', $query);

        $query = urlencode($query);
        $query = str_replace("+", "%20", $query);

        $query_uri = $endpoint . '?query=' . $query . '&format=' . urlencode("text/turtle");

        $response = $this->executeUri($query_uri, $user, $password);

        $result = new \EasyRdf_Graph();
        $parser = new \EasyRdf_Parser_Turtle();

        $parser->parse($result, $response, 'turtle', null);

        return $result;
    }

    /**
     * Create and perform the count query based on the original SPARQL query
     *
     * @param $query    string The SPARQL query
     * @param $endpoint string The endpoint to perform the query against
     * @param $user     string The user that has read permissions on the endpoint
     * @param $password string The password of the user
     *
     * @return integer
     */
    private function performCountQuery($query, $endpoint, $user, $password)
    {
        // Process the query through a set of regular expressions

        // Then replace the select statement with a count statement

        if (stripos($query, "select") !== false) { // SELECT query

            $keyword = "select";
            $this->query_type = "select";
        } elseif (stripos($query, "construct") !== false) { // CONSTRUCT query

            $keyword = "construct";
            $this->query_type = "construct";
        } else { // No valid SPARQL keyword has been found, is checked during validation
            \App::abort(500, "No CONSTRUCT or SELECT statement has been found in the given query: $query");
        }

        // Make a distinction between select and construct since
        // construct will be followed by a {} sequence, whereas a select statement will not
        $prefix = '';
        $filter = '';

        // Covers FROM <...> FROM <...> WHERE{ } , FROM <...> FROM <...> { }, WHERE { }, { }
        $where_clause = '(.*?(FROM.+?{.+})|.*?(WHERE.*{.+})|.*?({.+}))[a-zA-Z0-9]*?';

        $matches = array();

        if ($keyword == 'select') {

            $regex = $keyword . $where_clause;

            preg_match_all("/(.*)$regex/msi", $query, $matches);
        } else {

            preg_match_all("/(.*)$keyword(\s*\{[^{]+\})$where_clause/mis", $query, $matches);
        }

        $prefix = $matches[1][0];
        $filter = "";

        // Preg match all has 3 entries for the where clause, pick the first hit
        if (!empty($matches[3][0])) {
            $filter = $matches[3][0];
        }

        if (!empty($matches[4][0])) {
            $filter = $matches[4][0];
        }

        if (!empty($matches[5][0])) {
            $filter = $matches[5][0];
        }

        if (empty($filter)) {
            \App::abort(500, "Failed to retrieve the where clause from the query: $query");
        }

        // Prepare the query to count results
        $count_query = $matches[1][0] . ' SELECT (count(*) AS ?count) ' . $filter;

        $count_query = urlencode($count_query);
        $count_query = str_replace("+", "%20", $count_query);

        $count_uri = $endpoint . '?query=' . $count_query . '&format=' . urlencode("application/sparql-results+json");

        $response = $this->executeUri($count_uri, $user, $password);
        $response = json_decode($response);

        // If something goes wrong, the resonse will either be null or false
        if (!$response) {
            \App::abort(500, "Something went wrong while executing the count query. The count URI was: $count_uri");
        }

        return $response->results->bindings[0]->count->value;
    }

    /**
     * Execute a query using cURL and return the result.
     * This function will abort upon error.
     *
     * @param $uri      string The URI to GET
     * @param $user     string The user to pass as basic authentication
     * @param $password string The password of the user
     */
    private function executeUri($uri, $user = '', $password = '')
    {
        // Check if curl is installed on this machine
        if (!function_exists('curl_init')) {
            \App::abort(500, "cURL is not installed as an executable on this server, this is necessary to execute the SPARQL query properly.");
        }

        // Initiate the curl statement
        $ch = curl_init();

        // If credentials are given, put the HTTP auth header in the cURL request
        if (!empty($user)) {

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $password);
        }

        // Set the request uri
        curl_setopt($ch, CURLOPT_URL, $uri);

        // Request for a string result instead of having the result being outputted
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);

        if (!$response) {
            $curl_err = curl_error($ch);
            \App::abort(500, "Something went wrong while executing query. The request we put together was: $uri.");
        }

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // According to the SPARQL 1.1 spec, a SPARQL endpoint can only return 200,400,500 reponses
        if ($response_code == '400') {
            \App::abort(500, "The SPARQL endpoint returned a 400 error. If the SPARQL query contained a parameter, don't forget to pass them as a query string parameter. The error was: $response. The URI was: $uri");
        } elseif ($response_code == '500') {
            \App::abort(500, "The SPARQL endpoint returned a 500 error. If the SPARQL query contained a parameter, don't forget to pass them as a query string parameter. The URI was: $uri");
        }

        curl_close($ch);

        return $response;
    }
}
