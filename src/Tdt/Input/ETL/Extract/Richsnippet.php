<?php

namespace Tdt\Input\ETL\Extract;

use EasyRdf\Graph;
use EasyRdf\Parser\Rdfa as RdfaParser;
use EasyRdf\Parser\JsonLd;

class Richsnippet extends AExtractor
{
    protected function open()
    {
        $this->graph = new Graph();
        $rdfa_parser = new RdfaParser();
        $json_parser = new JsonLd();

        \EasyRdf\RdfNamespace::set('dcat', 'http://www.w3.org/ns/dcat#');
        \EasyRdf\RdfNamespace::set('lbld', 'http://decisions.data.vlaanderen.be/ns#');

        $data = file_get_contents($this->extractor->uri);

        // Assume we have an rdfa document to begin with
        $rdfa_parser->parse($this->graph, $data, 'rdfa', $this->extractor->base_uri);

        // Check if we have properties we need to include into the current graph
        $properties = explode(',', $this->extractor->follow_properties);

        foreach ($this->graph->resources() as $resource) {
            foreach ($properties as $property) {
                $resolve_resources = $resource->allResources($property);

                if (!empty($resolve_resources)) {
                    foreach ($resolve_resources as $resolve_resource) {
                        $data = file_get_contents($resolve_resource->getUri());

                        // Parse any rdfa data in the document into the existing graph
                        $this->graph->parse($data, 'rdfa', $resolve_resource->getUri());

                        $json = $this->parseJsonldSnippet($data);

                        if (!empty($json)) {
                            try {
                                $this->graph->parse($json, 'jsonld', $resolve_resource->getUri());
                            } catch (\Exception $ex) {
                                \Log::error("We could not parse json from the data, the data was:" . $json);
                            }
                        }
                    }
                }
            }
        }

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

    /**
     * Parse a JSON-LD snippet from an HTML page
     *
     * @param string $html
     *
     * @return string The JSON-LD snippet
     */
    private function parseJsonldSnippet($html)
    {
        $primer = preg_quote("<script application/ld+json>", '/');
        $end = preg_quote("</script>", '/');

        preg_match("/.*\<script.*?application\/ld\+json.*?\>(.+?)$end.*/", $html, $result);

        if (!empty($result[1])) {
            return $result[1];
        }

        return null;
    }
}
