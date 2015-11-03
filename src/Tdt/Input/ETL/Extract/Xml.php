<?php

namespace Tdt\Input\ETL\Extract;

class XML extends AExtractor
{
    use Encoding;

    private $next;
    private $reader;
    private $index = 0;

    protected function open()
    {
        $uri = $this->extractor->uri;
        $this->encoding = $this->extractor->encoding;

        $this->reader = new \XMLReader();

        if (!$this->reader->open($uri)) {
            $this->log("The uri ($uri) could not be opened. Make sure it is retrievable by the server.");
        }

        $arraylevel = $this->extractor->arraylevel;


        for ($i = 0; $i < $arraylevel; $i++) {
            $this->reader->read();
        }
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        if ($this->reader->next()) {
            $this->next = $this->reader->readOuterXML();

            if (empty($this->next)) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop()
    {
        if (!empty($this->next) && !empty(trim($this->next))) {
            $xml_string = $this->next;

            if ($this->encoding != 'UTF-8') {
                $xml_string = $this->convertToUtf8($xml_string, $this->encoding);
            }

            $xml_string = $this->fixUtf8($xml_string);
            $xml = simplexml_load_string($xml_string);

            return get_object_vars($xml);
        } else {
            return null;
        }

    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
        $this->reader->close();
    }
}
