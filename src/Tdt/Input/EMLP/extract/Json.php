<?php

namespace Tdt\Input\EMLP\Extract;

use Tdt\Input\EMLP\Helper\Json\JsonProcessor;
use Tdt\Input\EMLP\Helper\Json\Parser;

class Json extends AExtractor
{

    private $handle;

    private $parser;
    private $listener;

    protected function open()
    {

        $uri = $this->extractor->uri;
        $this->listener = new JsonProcessor();
        $this->parser = new Parser($this->listener);
        $this->parser = new \tdt\json\JSONCharInputReader($this->listener);

        $this->handle = fopen($uri, 'r');
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
    }
}
