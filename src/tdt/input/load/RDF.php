<?php

namespace tdt\input\load;
use RedBean_Facade as R;

class RDF extends \tdt\input\ALoader {

    private $endpoint;
    private $format;
    private $buffer_size; //amount of chunks that are being inserted into one request
    //helper vars
    private $buffer = array();

    /**
     * validation already done earlier
     */
    public function __construct($config) {
        if (!isset($config["endpoint"]))
            throw new \Exception('SPARQL endpoint not set in config');
        $this->endpoint = $config["endpoint"];
        $this->format = "json";

        if (!isset($config["datatank_uri"]))
            throw new \Exception('Destination datatank uri not set in config');
        
        $this->datatank_uri = $config["datatank_uri"];

        if (!isset($config["datatank_package"]))
            throw new \Exception('Destination datatank package not set in config');
        
        $this->datatank_package = $config["datatank_package"];

        if (!isset($config["datatank_resource"]))
            throw new \Exception('Destination datatank resource not set in config');
        
        $this->datatank_resource = $config["datatank_resource"];
        
        if (!isset($config["datatank_user"]))
            throw new \Exception('User for datatank not set in config');
        
        $this->datatank_user = $config["datatank_user"];
        
        if (!isset($config["datatank_password"]))
            throw new \Exception('Password for datatank not set in config');
        
        $this->datatank_password = $config["datatank_password"];
        if (!isset($config["graph"]))
            throw new \Exception('Destination graph not set in config');

        $this->graph = $graph_id;

        if (!isset($config["buffer_size"]))
            $config["buffer_size"] = 25;

        $this->buffer_size = $config["buffer_size"];
    }

    public function __destruct() {
        echo "Empty loader buffer...\n";
        
        try{
            while (!empty($this->buffer)) {
                $count = count($this->buffer) <= $this->buffer_size ? $this->buffer_size : count($this->buffer);
                
                $triples_to_send = array_slice($this->buffer, 0, $count);
                $this->query(implode(' ', $triples_to_send));
                
                $this->buffer = array_slice($this->buffer, $count);
            }
        }
        catch(\Exception $e){
            echo "ETML Failed: " . $e->getMessage() . "\n";
        }

        echo "Inserting resource into datatank...\n";
        //Add SPARQL resource with describe query to datatank
        $data = array(
            "resource_type" => "generic",
            "generic_type" => "ld",
            "endpoint" => $this->endpoint,
            "documentation" => "Linked Data resource inserted by tdt/input for the retrieval of URIs in $this->datatank_package/$this->datatank_resource"
        );
        
        //Build PUT uri for datatank
        $uri = $this->datatank_uri . "TDTAdmin/Resources/$this->datatank_package/$this->datatank_resource";

        $ch = curl_init($uri);
        
        curl_setopt($ch, CURLOPT_USERPWD, $this->datatank_user . ":" . $this->datatank_password);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        if ($response_code >= 400) 
            throw new \Exception("PUT request to The DataTank instance for package: $this->datatank_package and resource: $this->datatank_resource failed!");
        
        echo "Resources available under " . $this->datatank_uri . "$this->datatank_package/$this->datatank_resource/\n";
    }

    public function execute(&$chunk) {
        $start = microtime(true);

        if (!$chunk->is_empty()) {
            preg_match_all("/(<.*\.)/", $chunk->to_ntriples(), $matches);
            if ($matches[0])
                $this->buffer = array_merge($this->buffer, $matches[0]);


            while (count($this->buffer) >= $this->buffer_size) {
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
        $graph = $this->datatank_uri . $this->datatank_package . "/" . $this->datatank_resource . "/";
        
        $serialized = preg_replace_callback('/(?:\\\\u[0-9a-fA-Z]{4})+/', function ($v) {
                $v = strtr($v[0], array('\\u' => ''));
                return mb_convert_encoding(pack('H*', $v), 'UTF-8', 'UTF-16BE');
            }, $triples);

        $query = "INSERT IN GRAPH <$graph> { ";
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
        {
            echo "endpoint returned error: " . curl_error($ch) . " - ";
            throw new \Exception("Endpoint returned an error!");
        }
        
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response_code != "200")
        {
            echo "query failed: " . $response_code . "\n" . $response . "\n";
            throw new \Exception("Query failed!");
        }


        curl_close($ch);

        return $response;
    }

}
