<?php

namespace Tdt\Input\Controllers;

use Tdt\Input\Repositories\Interfaces\JobLogRepositoryInterface;

class LogController extends \Controller
{
    public function __construct(JobLogRepositoryInterface $logs)
    {
        $this->logs = $logs;
    }

    public function get($identifier)
    {
        return $this->logs->getLastLogsFromJob($identifier);
    }
}
