<?php

namespace tdt\input\emlp\extract;

class Csv extends AExtractor{

    private $handle;

    private $header;

    protected function open(){

        $uri = $this->extractor->uri;

        // Open a filehandle for the uri
        $this->handle = fopen($uri, 'r');

        if($this->extractor->has_header_row && ($data = fgetcsv($this->handle, 0, $this->extractor->delimiter)) !== FALSE){

            $i=0;
            foreach($data as &$el){
                $this->header[$i] = $el;
                $i++;
            }
        }
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext(){
        return !feof($this->handle);
    }

    /**
     * Gives us the next chunk to process through our emlp
     * @return a chunk in a php array
     */
    public function pop(){

        $row;

        if(($data = fgetcsv($this->handle, 0, $this->extractor->delimiter)) !== FALSE){

            $i=0;

            foreach($data as &$el){

                if($this->extractor->has_header_row){
                    $row[$this->header[$i]] = $el;
                }

                $row[$i] = $el;
                $i++;
            }
        }
        return $row;
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close(){
        fclose($this->handle);
    }
}
