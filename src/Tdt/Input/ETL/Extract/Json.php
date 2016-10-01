<?php

namespace Tdt\Input\ETL\Extract;

use Tdt\Input\ETL\Helper\Json\JsonProcessor;
use Tdt\Input\ETL\Helper\Json\Parser;

class Json extends AExtractor
{

    private $handle;

    private $parser;
    private $listener;

    protected function open()
    {
        $this->uri = $this->extractor->uri;
        $this->listener = new JsonProcessor();

        // Check if we have to download the file before processing it
        $this->is_uri_tmp_file = false;

        // Open a filehandle for the uri
        $ssl_options = array(
                            "ssl"=>array(
                                "verify_peer"=>false,
                                "verify_peer_name"=>false,
                                ),
                            );

        if (substr($this->uri, 0, 4) == "http") {
            $tmp_file = sys_get_temp_dir() . "/" . uniqid() . '.csv';

            file_put_contents($tmp_file, file_get_contents($this->uri, false, stream_context_create($ssl_options)));

            $this->uri = $tmp_file;
            $this->is_uri_tmp_file = true;
        }

        $this->handle = fopen($this->uri, 'r', false, stream_context_create($ssl_options));

        if (!$this->handle) {
            $this->log("Could not open the file with location $this->uri.");
            die;
        }

        $this->parser = new \tdt\json\JSONCharInputReader($this->listener);
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
     * Gives us the next chunk to process through our ETML
     * @return a chunk from the json document or NULL
     */
    public function pop()
    {
        while (!$this->listener->hasNew() && !feof($this->handle)) {
            $char = fread($this->handle, 1);

            if ($char !== "" && $char != "\n") {
                $this->parser->readChar($char);
            }
        }

        if ($this->listener->hasNew()) {
            return $this->listener->pop();
        }
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
        fclose($this->handle);

        if ($this->is_uri_tmp_file) {
            unlink($this->uri);
        }
    }
}
