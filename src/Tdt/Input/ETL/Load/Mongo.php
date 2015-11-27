<?php

namespace Tdt\Input\ETL\Load;

use MongoClient;

/**
 * The Sparql class loads triples into a triplestore.
 */
class Mongo extends ALoader
{
    public function __construct($model, $command)
    {
        parent::__construct($model, $command);
    }

    public function init()
    {
        // Initiate the timestamp
        $this->timestamp = time();

        $auth = [];

        if (!empty($this->loader['username'])) {
            $auth['username'] = $this->loader['username'];

            if (!empty($this->loader['password'])) {
                $auth['password'] = $this->loader['password'];
            }
        }

        $connString = 'mongodb://' . $this->loader['host'] . ':' . $this->loader['port'] . '/' . $this->loader['database'];

        $this->log("Creating mongo client with connection string: " . $connString, 'info');

        $client = new MongoClient($connString, $auth);

        $this->log("Connecting with the " . $this->loader['database'] . " and the ". $this->loader['collection'] . " collection.", 'info');

        $this->mongoCollection = $client->selectCollection($this->loader['database'], $this->loader['collection']);

        $this->mongoCollection->remove([]);
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
        try {
            $this->mongoCollection->save($chunk);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage());

            return false;
        } catch (\MongoException $ex) {
            $this->log($ex->getMessage());

            return false;
        }

        return true;
    }
}
