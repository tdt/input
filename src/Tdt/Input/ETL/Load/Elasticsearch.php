<?php

namespace Tdt\Input\ETL\Load;

use Carbon\Carbon;
use Elastica\Client;
use Elastica\Query;
use Elastica\Query\Range;
use Elastica\Index;

class Elasticsearch extends ALoader
{
    private static $ETL_TIMESTAMP = '__tdt_etl_timestamp__';

    public function __construct($model, $command)
    {
        parent::__construct($model, $command);

        // Initiate the start timestamp
        $dt = Carbon::now();
        $this->timestamp = $dt->toIso8601String();
    }

    public function init()
    {
        // Check for authentication
        if (!empty($this->loader['username']) && !empty($this->loader['password'])) {
            $auth = $this->loader['username'] . ':' . $this->loader['password'] . '@';

            $parts = parse_url($this->loader['host']);

            if (empty($parts['scheme'])) {
                $this->loader['host'] = 'http://' . $auth . $this->loader['host'];
            } elseif ($parts['scheme'] == 'https') {
                $schemeless_url = str_replace('https://', '', $this->loader['host']);
                $this->loader['host'] = 'https://' . $auth . $schemeless_url;
            } else {
                $schemeless_url = str_replace('http://', '', $this->loader['host']);
                $this->loader['host'] = 'http://' . $auth . $schemeless_url;
            }
        }

        $this->type = $this->loader['es_type'];

        $this->client = new Client(['host' => $this->loader['host'], 'port' => $this->loader['port']]);
        $this->index = new Index($this->client, $this->loader['es_index']);

        if (!$this->index->exists()) {
            $this->index->create();
        }

        $this->log(
            "The ElasticSearch client is configured to write to the index " . $this->loader['es_index'] . " with the " . $this->loader['es_type'] . " type.",
            'info'
        );

        try {
            $this->index->create([
                'number_of_shards' => 2,
                'number_of_replicas' => 0
            ]);
        } catch (\Exception $ex) {
            $this->log("An error occured while trying to create the index, this is probably because it exists already", 'warning');
            $this->log("The message was: " . $ex->getMessage(), 'warning');
        }

        $this->type = $this->index->getType($this->loader['es_type']);

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($this->type);

        // Set mapping
        $mapping->setProperties(array(
            self::$ETL_TIMESTAMP => array('type' => 'date')
        ));

        // Send mapping to type
        $mapping->send();
    }

    /**
     * Remove all outdated documents in the index of the configured type
     *
     * @return void
     */
    public function cleanUp()
    {
        $dateRange = new Range();
        $dateRange->addField(self::$ETL_TIMESTAMP, array('lt' => $this->timestamp));

        $query = new Query();
        $query->setQuery($dateRange);

        $this->type->deleteByQuery($query);

        $this->log("Removed outdated documents that were outdated.");
    }

    /**
     * Perform the load.
     *
     * @param mixed $chunk
     * @return bool
     */
    public function execute($chunk)
    {
        $chunk[self::$ETL_TIMESTAMP] = $this->timestamp;

        $tweetDocument = new \Elastica\Document('', $chunk);

        try {
            $this->type->addDocument($tweetDocument);
            $this->type->getIndex()->refresh();
        } catch (\Exception $ex) {
            $this->log("Something went wrong while adding a document ( " . json_encode($chunk) . ")",  'error');

            return false;
        }

        return true;
    }
}
