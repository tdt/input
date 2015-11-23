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

        $prefix = '';

        if (!empty($this->loader['username'])) {
            $prefix = $this->loader['username'] . $this->loader['password'];
        }

        $connString = 'mongodb://' . $prefix . $this->loader['host'] . ':' . $this->loader['port'];

        $this->log('info', "Creating mongo client with connection string: " . $connString);

        $client = new MongoClient($connString);

        $this->log('info', "Connecting with the " . $this->loader['database'] . " and the ". $this->loader['collection'] . " collection.");

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
