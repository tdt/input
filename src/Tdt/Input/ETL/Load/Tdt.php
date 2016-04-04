<?php

namespace Tdt\Input\ETL\Load;

use EasyRdf\Serialiser\Turtle;

/**
 * The Sparql class loads triples into a triplestore.
 */
class Tdt extends ALoader
{
    public function __construct($model, $command)
    {
        parent::__construct($model, $command);

        $this->definitions = \App::make('Tdt\Core\Repositories\Interfaces\DefinitionRepositoryInterface');
    }

    public function init()
    {
    }

    public function cleanUp()
    {
    }

    /**
     * Perform the load.
     *
     * @param mixed $chunk
     * @return void
     */
    public function execute($chunk)
    {
        if (is_array($chunk)) {
            $definition = $this->processRemoteDataset($chunk['dataset']);
            $definition['original_document'] = $chunk['original_document'];

            return $this->definitions->store($definition);
        } else {
            $definition = $this->processRemoteDataset($chunk);

            return $this->definitions->store($definition);
        }
    }

    private function processRemoteDataset($chunk)
    {
        // Check for the correc type (dcat:Dataset)
        if ($chunk->type() == "dcat:Dataset") {
            $properties = [];

            // Parse normal single value dcat properties, a TDT instance can store
            $properties_map = $this->getSinglePropertiesMap();

            $identifier = $chunk->getLiteral('dc:identifier')->getValue();

            $chunks = explode('/', $identifier);

            $properties['resource_name'] = strtolower(array_pop($chunks));
            $collection_uri = strtolower('harvested/' . implode('/', $chunks));

            $properties['collection_uri'] = rtrim($collection_uri, '/');
            $properties['type'] = $this->loader->definition_type;
            $properties['dcat'] = $chunk->getGraph()->serialise('ttl');

            return $properties;
        } else {
            $this->log("We received a chunk of type " . $chunk->type() . " which is not a DCAT dataset");
        }
    }

    private function getSinglePropertiesMap()
    {
        return [
            'title' => 'dc:title',
            'description' => 'dc:description',
            'theme' => 'dcat:theme',
            'contact_point' => 'dcat:contactPoint',
            'language' => 'dc:language'
        ];
    }
}
