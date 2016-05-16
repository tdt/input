<?php

namespace Tdt\Input\ETL\Extract;

use EasyRdf\Graph;
use EasyRdf\Parser\RdfXml;
use DOMDocument;
use XSLTProcessor;

class Inspire extends AExtractor
{
    protected function open()
    {
        // Perform XLST
        $xsl_url = "https://webgate.ec.europa.eu/CITnet/stash/projects/ODCKAN/repos/iso-19139-to-dcat-ap/browse/iso-19139-to-dcat-ap.xsl?raw";

        $proc = null;

        try {
            $xml = new DOMDocument;
            $xml->load($this->extractor->uri);

            $xsl = new DOMDocument;
            $xsl->load($xsl_url);

            $proc = new XSLTProcessor();
            $proc->importStyleSheet($xsl);

        } catch (\ErrorException $ex) {
            $this->log('Something went wrong: ' . $ex->getMessage());
            die;
        }

        $dcat_document = $proc->transformToXML($xml);

        \EasyRdf\RdfNamespace::set('locn', 'http://www.w3.org/ns/locn#');

        // Parse the dcat graph
        $graph = new Graph();

        $rdf_parser = new RdfXml();

        $rdf_parser->parse($graph, $dcat_document, 'rdfxml', 'http://foo');

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
        return ['dataset' => array_shift($this->datasets), 'original_document' => $this->extractor->uri];
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
    }
}
