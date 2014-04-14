<?php

namespace Tdt\Input\EMLP\Extract;

class Csv extends AExtractor
{

    private $handle;
    private $row_index;
    private $header;

    protected function open()
    {

        $uri = $this->extractor->uri;

        // Keep track at which row the Csv handler is
        $this->row_index = 0;

        // Open a filehandle for the uri
        $this->handle = fopen($uri, 'r');

        if (!$this->handle) {
            $this->log("Could not open the file with location $uri.");
            die();
        }

        $this->log("Opened the CSV file located at $uri");

        if ($this->extractor->has_header_row && ($data = fgetcsv($this->handle, 0, $this->extractor->delimiter)) !== false) {

            $i=0;

            foreach ($data as &$el) {
                $this->header[$i] = $el;
                $i++;
            }
            $this->row_index++;
        }
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        return !feof($this->handle);
    }

    /**
     * Gives us the next chunk to process through our emlp
     * @return a chunk in a php array
     */
    public function pop()
    {

        $row = array();

        if (($data = fgetcsv($this->handle, 0, $this->extractor->delimiter)) !== false) {

            $i=0;

            foreach ($data as &$el) {

                if ($this->extractor->has_header_row) {
                    $row[$this->header[$i]] = $el;
                }

                $row[$i] = $el;
                $i++;
            }
        }

        $this->log("Extracted data from row $this->row_index");

        return $row;
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
        fclose($this->handle);
    }
}
