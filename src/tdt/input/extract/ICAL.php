<?php

namespace tdt\input\extract;

class ICAL extends \tdt\input\AExtractor {

    private $handle;
    private $element = "VEVENT";

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
    private function parseLine() {
        $hash = array();
        $line = explode(':', fgets($this->handle));
        $key = array_shift($line);

        $hash[$key] = trim(implode(":", $line));
        
        return $hash;
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop() {
        $row = array();

        //Move to first BEGIN of the selected element
        while ($this->hasNext()) {
            $line = $this->parseLine();
            
            if (!array_key_exists("BEGIN", $line))
                continue;

            if (trim($line["BEGIN"]) == $this->element) {
                $row = $line;
                break;
            }
        }

        //Add all values
        while ($this->hasNext()) {
            $line = $this->parseLine();
            $row = array_merge($row, $line);

            if (!array_key_exists("END", $line))
                continue;

            if (trim($line["END"]) === $this->element)
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
