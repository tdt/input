<?php

namespace Tdt\Input\Repositories\Interfaces;

/**
 * The interface for the job log repository.
 *
 * @author Jan Vansteenlandt
 */
interface JobLogRepositoryInterface
{
    /**
     * Get all job logs with given limit and offset
     *
     * @param integer $limit  The amount of logs to return
     * @param integer $offset The offset in the logs
     *
     * @return array
     */
    public function getAll($limit, $offset);

    /**
     * Get the last logs from a job based on the execution timestamp
     *
     * @param string $identifier The job identifier
     *
     * @return array
     */
    public function getLastLogsFromJob($identifier);

    /**
     * Remove all the job logs for a given job
     *
     * @param string $identifier The job identifier of which all logs must be deleted
     *
     * @return void
     */
    public function deleteAllLogsFromJob($identifier);

    /**
     * Add a log for a job
     *
     * @param string $identifier The job identifier to add a log for
     *
     * @return void
     */
    public function addLogForJob($identifier);
}
