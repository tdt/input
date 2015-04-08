<?php

namespace Tdt\Input\EMLP\Load;

use \ML\JsonLD\JsonLD;

/**
 * The Mongo class loads json-ld into a mongo document store.
 */
class Mongo extends ALoader
{

    public function __construct($model, $command)
    {
        parent::__construct($model, $command);

        // Get the namespaces repository
        $ns_repo = \App::make('Tdt\Core\Repositories\Interfaces\OntologyRepositoryInterface');

        $this->log('Create the context for the jsonld document.');

        // Extracting namespaces for the json ld document
        $ns = $ns_repo->getAll();

        // Build the context
        $context = new \stdClass();
        $namespaces = new \stdClass();

        $context_keyword = '@context';

        foreach ($ns as $namespace) {
            $namespaces->$namespace['prefix'] = $namespace['uri'];
        }

        $context->$context_keyword = $namespaces;

        $this->context = $context;

        // Set the timestamp and set the datasource
        $this->timestamp = time();

        $this->log("Setting the timestamp: " . $this->timestamp);

        $this->source = $command->argument('jobname');

        $this->log("Setting the source: " . $this->source);

        // Keep track of the @id's so we can perform the cleaning up
        $this->ids = array();

        // Set the model to map to
        $this->model_name = 'Ilastic\\' . ucfirst(strtolower($model->model));
    }

    public function init()
    {
    }

    /**
     * After the loader has been called upon his last execute() method, triples might still remain in the buffer.
     * If so, load the remaining of them into the triple store.
     */
    public function cleanUp()
    {
        $this->log("Starting the clean up.");

        $this->log("Removing old entries from the collection.");

        // Delete the old entries
        $model = new $this->model_name();

        $delete_result = $model
            //->whereIn('@id', $this->ids)
            ->where('schema:datePublished', '<', $this->timestamp)
            ->where('source:source', $this->source)
            ->delete();

        // Because of possible partial graph results in a construct query we might encounter
        // documents in the store that have the same subject, but are divided in 2 or more documents.
        // Therefore we'll join those divided documents into one and delete the other partial documents.

        // Find all the results that have two or more occurences (@id field)

        // Iterate over id slices - instead of all the ids -
        // of the large sets of data, we might get an internal object coming from
        // the aggregation framework larger than 16MB, which results in an error.

        $ids = $this->ids;

        $collection = $model->getConnection()->getCollection($model->getTable());

        $this->log("Aggregating the partial graph results in the collection " . $model->getTable());

        while (!empty($ids)) {

            $rest_ids = array_splice($ids, 5);

            $this->log("Cleaning up for the following ids: " . implode(', ', $ids));

            $slice = array(
                '$match' => array(
                    '@id' => array(
                        '$in' => $ids
                    )
                )
            );

            $group = array(
                        '$group' => array(
                            '_id' => "$@id",
                            'count' => array(
                                '$sum' => 1
                            ),
                            'document' => array(
                                '$addToSet' => '$$ROOT'
                            )
                        )
                    );

            $match = array(
                        '$match' => array(
                            'count' => array(
                                '$gte' => 2
                            )
                        )
                    );

            $results = $collection->aggregate($slice, $group, $match);

            $results = $results['result'];

            // For each of the results, merge them, insert the resulting document and delete the other partial documents
            foreach ($results as $result) {

                $document = array();

                foreach ($result['document'] as $partial_result) {

                    $document = array_merge($document, $partial_result);
                }

                unset($document['document']);
                unset($document['_id']);

                // Update the collection with the merged document
                $this->log("Removing the partial graphs for @id " . $document['@id'] . ".");

                $delete_result = $model
                    ->where('@id', $document['@id'])
                    ->where('schema:datePublished', $this->timestamp)
                    ->where('source:source', $this->source)
                    ->delete();

                $this->log("Inserting the new document for @id " . $document['@id']);

                $this->insertDocument($document, $collection);
            }

            $ids = $rest_ids;
        }
    }

    /**
     * Perform the load.
     *
     * @param \EasyRdf_Graph $graph
     *
     * @return void
     */
    public function execute(&$graph)
    {
        $this->log("Processing new graph. Starting: " . time());

        $serializer = new \EasyRdf_Serialiser_JsonLd();

        $jsonld = $serializer->serialise($graph, 'jsonld');

        $jsonld = JsonLD::expand($jsonld);

        // For every result in the expanded document, compact it
        // because uri's contain dots which are not allowed as a key character
        // in mongodb
        foreach ((array) $jsonld as $document) {

            // Add the meta-data for tracking purposes (needed to delete old entries e.g.)
            $timestamp = 'http://schema.org/datePublished';
            $source = 'http://foo.bar/source';

            $this->log("Adding the timestamp and source to the document.");

            $document->$timestamp = $this->timestamp;
            $document->$source = $this->source;

            $compact_document = JsonLD::compact($document, $this->context);

            $this->insertDocument((array) $compact_document);
        }

        $this->log("Done loading graph. Ended at: " . time());
    }

    /**
     * Insert the JSON-LD into the document store
     * Before that happens, remove all the entries with
     * the same timestamp and @id
     *
     * @param array $document
     *
     * @return void
     */
    private function insertDocument($document)
    {
        // Add the @id in the collection of ids
        $this->ids[] = $document['@id'];

        $model_instance = new $this->model_name($document);

        foreach ($document as $key => $val) {
            $model_instance->$key = $val;
        }

        $model_instance->save();
    }
}
