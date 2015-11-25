<?php

namespace Tdt\Input\ETL\Load;

use Elasticsearch\Client;
use Carbon\Carbon;

class Elasticsearch extends ALoader
{
    private static $ETL_TIMESTAMP = 'tdt_etl_timestamp_';

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

        $hosts = ['hosts' => [$this->loader['host'] . ':' . $this->loader['port']]];
        $this->client = new Client($hosts);

        $this->type = $this->loader['es_type'];
        $this->index = $this->loader['es_index'];

        $this->log("The ElasticSearch client is configured to write to the index " . $this->loader['index'] . " with the ". $this->loader['type'] . " type.", 'info');

        $indexParams = [];
        $indexParams['index']  = $this->index;

        if (!$this->client->indices()->exists($indexParams)) {
            $newIndexParams = [
                'index' => $this->index,
                'type' => $this->type,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 2,
                        'number_of_replicas' => 0
                    ],
                    'mappings' => [
                        $this->type => [
                            'properties' => [
                                '_timestamp' => [
                                    'enabled' => true,
                                    'path' => self::$ETL_TIMESTAMP
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->client->create($newIndexParams);
        }
    }

    public function cleanUp()
    {
        // Remove the old data
        $params = array(
            "search_type" => "scan",    // use search_type=scan
            "scroll" => "1s",          // how long between scroll requests. should be small!
            "size" => 100,               // how many results *per shard* you want back
            "index" => $this->index,
            "type" => $this->type,
            "body" => array(
                "query" => array(
                    "range" => array(
                        self::$ETL_TIMESTAMP => array(
                            "lt" => $this->timestamp
                            )
                        )
                    )
                )
        );

        // Count how many documents we delete
        $counter = 0;

        $docs = $this->client->search($params);   // Execute the search
        $scroll_id = $docs['_scroll_id'];   // The response will contain no results, just a _scroll_id

        // Now we loop until the scroll "cursors" are exhausted
        $response = $this->client->scroll([
                    "scroll_id" => $scroll_id,
                    "scroll" => "1s"
                    ]);

        // Check to see if we got any search hits from the scroll
        while (count($response['hits']['hits']) > 0) {
            foreach ($response['hits']['hits'] as $document) {
                $this->client->delete([
                    'index' => $this->index,
                    'type' => $this->type,
                    'id' => $document['_id']
                    ]);

                $counter++;
            }

            // Refresh the scroll_id, it's subject to change
            $scroll_id = $response['_scroll_id'];

            $response = $this->client->scroll([
                    "scroll_id" => $scroll_id,
                    "scroll" => "1s"
                    ]);
        }

        $this->log("Removed $counter documents that were outdated.");
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

        try {
            $response = $this->client->index([
                'index' => $this->index,
                'type'  => $this->type,
                'body' => $chunk
            ]);

            $this->log("Added the datachunk, returned id was " . $response['_id']);
            return true;

        } catch (\Exception $ex) {
            $this->log("Could not add the data, something went wrong: " . $ex->getMessage());

            return false;
        }
    }
}
