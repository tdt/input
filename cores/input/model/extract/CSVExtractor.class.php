<?php

class CSVExtractor extends AExtractor{

    private $handle;

    protected function open($url){
        $this->handle = fopen('php://temp', 'w+');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
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
        $row;
        if( ($data = fgetcsv($this->handle, 1000, $this->config["delimiter"])) !== FALSE) {
            $i=0;
            foreach($data as &$el){
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
