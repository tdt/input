<?php

namespace Tdt\Input\ETL\Extract;

use EasyRdf\Graph;
use EasyRdf\Parser\Rdfa as RdfaParser;

class Rdfa extends AExtractor
{
    protected function open()
    {
        $graph = new Graph();
        $parser = new RdfaParser();

        $data = file_get_contents($this->extractor->uri);

        $parser->parse($graph, $data, 'rdfa', 'http://foobar.com');

        $this->resources = $graph->resources();
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        return !empty($this->resources);
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk from the json document or NULL
     */
    public function pop()
    {
        return array_shift($this->resources);
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
    }
}
