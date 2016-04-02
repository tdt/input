<?php

namespace Tdt\Input\ETL\Extract;

use EasyRdf\Graph;

class Geodcat extends AExtractor
{
    protected function open()
    {
        \EasyRdf\RdfNamespace::set('locn', 'http://www.w3.org/ns/locn#');

        $graph = Graph::newAndLoad($this->extractor->uri, $this->extractor->format);

        $this->datasets = $graph->allOfType('dcat:Dataset');
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        return !empty($this->datasets);
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk from the json document or NULL
     */
    public function pop()
    {
        return array_shift($this->datasets);
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
    }
}
