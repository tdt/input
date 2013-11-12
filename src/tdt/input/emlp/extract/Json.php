<?php

namespace tdt\input\emlp\extract;

use tdt\input\emlp\helper\JSONInputProcessor;
use tdt\json\JSONCharInputReader;

class Json extends AExtractor{

    private $handle, $reader, $processor;

    protected function open(){

        $uri = $this->extractor->uri;

        $this->processor = new JSONInputProcessor();
        $this->reader = new JSONCharInputReader($this->processor);
        $this->handle = fopen('php://temp', 'w+');

        // Prepare the http request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_FILE, $this->handle);
        curl_exec($curl);
        curl_close($curl);

        rewind($this->handle);
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext(){
        return !feof($this->handle);
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop(){

        while(!$this->processor->hasNew() && !feof($this->handle)){

            $char = fread($this->handle, 1);
            if($char !== "" && $char != "\n"){
                $this->reader->readChar($char);
            }
        }

        if($this->processor->hasNew()){
            return $this->processor->pop();
        }else{
            return null;
        }
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close(){
        fclose($this->handle);
    }

}
