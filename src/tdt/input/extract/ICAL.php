<?php

namespace tdt\input\extract;

class CSV extends \tdt\input\AExtractor {

    private $handle;
    private $begin;

    protected function open($url) {
        $this->handle = fopen('php://temp', 'w+');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FILE, $this->handle);
        curl_exec($curl);
        curl_close($curl);
        rewind($this->handle);

        $this->begin = $this->parseLine();
        while (!$this->begin['BEGIN'])
            $this->begin = $this->parseLine();
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
    private function parseLine($line){
        $line = explode(':', fgets($this->handle));
        if (empty($line))
            return array();
        
        return array($line[0] => $line[1]);
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop() {
        
        $row = $this->begin;

        while ($this->hasNext()) {
            $line = $this->parseLine();

            if ($line['BEGIN']) {
                $this->begin = $line;
                break;
            }
            
            array_merge($row, $line);
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
