<?php

namespace tdt\input\extract;

class CSV extends \tdt\input\AExtractor {

    private $handle;
    
    private $element = 'VEVENT';

    protected function open($url) {
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
    public function hasNext() {
        return !feof($this->handle);
    }

    /**
     * Parses a line containing key:value
     * @return an associative array with one row
     */
    private function parseLine(){
        return explode(':', fgets($this->handle));
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop() {
        $row = array();
        $line = $this->parseLine();
        
        while (empty($line))
            $line = $this->parseLine();
        
        while ($line[0] !== "BEGIN" &&  $line[1] !== $this->element && $this->hasNext())
            $line = $this->parseLine();

        while ($this->hasNext()) {
            $line = $this->parseLine();
            $key = array_shift($line);
            
            $row[$key] = implode(":", $row);

            if ($key !== "END" &&  $line[1] !== $this->element) 
                break;
        }

        return $row;
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close() {
        fclose($this->handle);
    }

}
