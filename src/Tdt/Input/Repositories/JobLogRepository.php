<?php

namespace Tdt\Input\Repositories;

use Tdt\Input\Repositories\Interfaces\JobLogRepositoryInterface;

class JobLogRepository implements JobLogRepositoryInterface
{
    /**
     * Get all job logs with given limit and offset
     *
     * @param integer $limit  The amount of logs to return
     * @param integer $offset The offset in the logs
     *
     * @return array
     */
    public function getAll($limit, $offset)
    {
        $collection = $this->getMongoCollection();

        $cursor = $collection->find();

        $cursor = $cursor->skip($offset)->limit($limit);

        $logs = array();

        while ($cursor->hasNext()) {
            array_push($logs, $cursor->getNext());
        }

        return $logs;
    }

    /**
     * Get the last logs from a job based on the execution timestamp
     *
     * @param string $identifier The job identifier
     *
     * @return array
     */
    public function getLastLogsFromJob($identifier)
    {
        $collection = $this->getMongoCollection();

        $cursor = $collection->find()->sort(array('execution_timestamp' => -1));

        $cursor = $cursor->limit(1);

        if ($cursor->hasNext()) {

            $max_ts_log = $cursor->getNext();

            $max_ts = $max_ts_log['execution_timestamp'];

            $cursor = $collection->find(
                array('execution_timestamp' => $max_ts),
                array('_id' => 0)
            );

            $logs = array();

            while ($cursor->hasNext()) {
                array_push($logs, $cursor->getNext());
            }

            return $logs;
        }

        return array();
    }

    /**
     * Remove all the job logs for a given job
     *
     * @param string $identifier The job identifier of which all logs must be deleted
     *
     * @return bool|array
     */
    public function deleteAllLogsFromJob($identifier)
    {
        $collection = $this->getMongoCollection();

        return $collection->remove(array('identifier' => $identifier));
    }

    /**
     * Add a log
     *
     * @param array $log The log to add to the collection
     *
     * @return void
     */
    public function addLogForJob($log)
    {
        $collection = $this->getMongoCollection();

        $collection->insert($log);
    }

    /**
     * Get the mongo collection
     *
     * @return /MongoCollection
     */
    private function getMongoCollection()
    {
        $client = new \MongoClient(\Config::get('input::mongolog.server'));

        $collection = $client->selectCollection(\Config::get('input::mongolog.database'), \Config::get('input::mongolog.collection'));

        return $collection;
    }
}
