<?php

namespace Tdt\Input\ETL\Load;

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

            // Parse normal dcat properties, a TDT instance can store
            $properties_map = $this->getPropertiesMap();

            $identifier = $chunk->getLiteral('dc:identifier')->getValue();

            $chunks = explode('/', $identifier);

            $properties['resource_name'] = strtolower(array_pop($chunks));
            $collection_uri = strtolower('harvested/' . implode('/', $chunks));

            $properties['collection_uri'] = rtrim($collection_uri, '/');
            $properties['type'] = $this->loader->definition_type;
            $properties['dataset_uri'] = $chunk->getUri();

            foreach ($properties_map as $key => $value) {
                $literal = $chunk->getLiteral($value);

                if (!empty($literal)) {
                    $properties[$key] = $literal->getValue();
                }
            }

            // Check for a spatial property
            $location_resource = $chunk->getResource('dc:spatial');

            if (!empty($location_resource)) {
                $location = ['labels' => [], 'geometries' => []];

                // Get the label(s)
                $labels = $location_resource->allLiterals('skos:prefLabel');

                $label_values = [];

                foreach ($labels as $label) {
                    $label_values[] = $label->getValue();
                }

                $location['labels'] = $label_values;

                $geometries = [];

                if (!empty($location_resource->allLiterals('locn:geometry'))) {
                    foreach ($location_resource->allLiterals('locn:geometry') as $geometry_literal) {
                        $geometry = [];

                        $geometry['geometry'] = $geometry_literal->getValue();

                        if ($geometry_literal->getDatatypeUri() == 'http://www.opengis.net/ont/geosparql#wktLiteral') {
                            $geometry['type'] = 'wkt';
                        } elseif ($geometry_literal->getDatatypeUri() == 'https://www.iana.org/assignments/media-types/application/vnd.geo+json') {
                            $geometry['type'] = 'geojson';
                        }

                        if (!empty($geometry['type'])) {
                            $geometries[] = $geometry;
                        }
                    }

                    $location['geometries'] = $geometries;
                }

                $properties['spatial'] = $location;
            }

            return $properties;
        } else {
            $this->log("We received a chunk of type " . $chunk->type() . " which is not a DCAT dataset");
        }
    }

    private function getPropertiesMap()
    {
        return [
            'title' => 'dc:title',
            'keywords' => 'dcat:keyword',
            'description' => 'dc:description',
            'theme' => 'dcat:theme',
            'contact_point' => 'dcat:contactPoint',
            'language' => 'dc:language'
        ];
    }
}
