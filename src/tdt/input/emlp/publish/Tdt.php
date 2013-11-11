<?php

namespace tdt\input\emlp\publish;

class Tdt{

    public function __construct($publisher){
        $this->publisher = $publisher;
    }

    /**
     * Publish the loaded data structure to the datatank
     */
    public function execute(){

        $uri = $this->publisher->uri;
        $user = $this->publisher->user;
        $pw = $this->publisher->pw;

        // Get the type of loader to figure which PUT parameters we have to pass with the request
        $job = $this->publisher->job()->first();
        $loader = $job->loader()->first();

        $loader_type = get_class($loader);

        if(stripos($loader_type, 'sparql')){

            // Initiate the curl request
            $ch = curl_init();

            // Construct the meta-data properties
            // Reconstruct the graph name to where the triples where loaded (based on how the Sparqlloader constructs it)
            $graph_name = $loader->hostname . '/' . $job->collection_uri . '/' . $job->name;

            $put = array(
                'description' => "Publication of the loaded triples from the graph identified by graph name: $graph_name.",
                'endpoint' => $loader->endpoint,
                'endpoint_user' => $loader->user,
                'endpoint_password' => $loader->password,
            );

            // Create the general configurations for the curl request
            $options = array(
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_URL => $uri,
                CURLOPT_HTTPAUTH => CURLAUTH_ANY,
                CURLOPT_USERPWD => $user . ":" . $pw,
                CURLOPT_FRESH_CONNECT => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FORBID_REUSE => 1,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_POSTFIELDS => json_encode($put),
                CURLOPT_HTTPHEADER => array("Content-Type: application/tdt.ld"),
            );

            // Set the configuration of the curl request
            curl_setopt_array($ch, $options);

            $this->log("Performing request to the datatank uri: $uri.");
            $response = curl_exec($ch);

            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->log("Response code gotten from the request: $response_code");

            if ($response_code >= 400) {
                $this->log("A non 200 response code was given (" . $response_code . ") the response message was: " . $response);
            }
        }else{
            $class = end(explode('\\', $loader_type));
            $this->log("We do not provide any support yet to publish data that was loaded into the provided loading type configured in the job ($class)");
        }
    }

    /**
     * Log something to the output
     */
    protected function log($message){

        $class = explode('\\', get_called_class());
        $class = end($class);

        echo "Publisher[" . $class . "]: " . $message . "\n";
    }
}
