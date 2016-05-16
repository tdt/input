<?php

namespace Tdt\Input\ETL\Extract;

use EasyRdf\Graph;
use EasyRdf\Parser\Rdfa as RdfaParser;

class Rdfa extends AExtractor
{
    protected function open()
    {
        $this->graph = new Graph();
        $parser = new RdfaParser();

        $data = file_get_contents($this->extractor->uri);

        $parser->parse($this->graph, $data, 'rdfa', $this->extractor->base_uri);

        // We only return the graph once as a full datastructure
        $this->has_next = true;
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        return $this->has_next;
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk from the json document or NULL
     */
    public function pop()
    {
        $this->has_next = false;

        return $this->graph;
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
    }
}
