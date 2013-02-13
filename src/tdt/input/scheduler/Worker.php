<?php

<<<<<<< HEAD
<<<<<<< HEAD
namespace tdt\input\scheduler;

=======
namespace tdt/input/scheduler;
>>>>>>> 45e471a107b3e30c3f1460d2647ae677f11ce6aa
=======
namespace tdt\input\scheduler;

>>>>>>> 1416a45e21a0e527edccb59de4dfd99f66589abe
/**
 * This class looks whether the queue contains things to execute
 */
class Worker{
    private $queue, $db;
    
    /**
     * @param $config is an array with db config
     */
    public function __construct(array $config){
        $this->db = $config;
    }

    public function execute(){
        $schedule = new Schedule($this->db);
        $schedule->execute();
    }
}
