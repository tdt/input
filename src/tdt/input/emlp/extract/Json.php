<?php

namespace tdt\input\emlp\extract;

/*use tdt\input\emlp\helper\JSONInputProcessor;
use tdt\json\JSONCharInputReader;*/
use tdt\input\emlp\helper\json\JsonProcessor;
use tdt\input\emlp\helper\json\Parser;

class Json extends AExtractor{

    private $handle, $reader, $processor;
   /* $stream = fopen($testfile, 'r');
try {
  $parser = new JsonStreamingParser_Parser($stream, $listener);
  $parser->parse();
} catch (Exception $e) {
  fclose($stream);
  throw $e;
}*/

    private $parser, $listener;

    protected function open(){

        $uri = $this->extractor->uri;
        $this->listener = new JsonProcessor();
        $this->parser = new Parser($this->listener);

        //$this->processor = new JSONInputProcessor();
        //$this->reader = new JSONCharInputReader($this->processor);
        $this->handle = fopen($uri, 'r');

        // Prepare the http request
        /*
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_FILE, $this->handle);
        curl_exec($curl);
        curl_close($curl);
        */
        //rewind($this->handle);
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

        /*while(!$this->processor->hasNew() && !feof($this->handle)){

            $char = fread($this->handle, 1);
            if($char !== "" && $char !== "\r\n"){
                echo $char;
                $this->reader->readChar($char);
            }
        }

        if(!feof($this->handle)){
            $this->log("Didnt reach end of file");
            var_dump($this->processor->pop());
        }else{
            $this->log("Reached end of file");
        }

        if($this->processor->hasNew()){
            return $this->processor->pop();
        }else{
            return null;
        }*/

        while (!$this->listener->hasNew() && !feof($this->handle)) {

            $line = stream_get_line($this->handle, 1);
            $byteLen = strlen($line);
            for ($i = 0; $i < $byteLen; $i++) {
                $this->parser->_consume_char($line[$i]);
            }
        }

        if($this->listener->hasNew()){
            return $this->listener->pop();
        }

    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close(){
        fclose($this->handle);
    }

}
