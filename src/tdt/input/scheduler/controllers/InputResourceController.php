<?php

/**
 * Can only be used when you have a tdt/core installed.
 *
 * Can be used as follows:
 * * AUTH GET http://data.example.com/TDTInput/
 *    → returns all the jobs configured
 * * AUTH GET http://data.example.com/TDTInput/{jobid}
 *    → returns the configuration of this job + its next duetime
 *  * AUTH POST http://data.example.com/TDTInput/
 *    → add a new job, returns the jobid
 *  * AUTH PUT http://data.example.com/TDTInput/{jobid}
 *    → overwrite a job
 */

namespace tdt\input\scheduler\controllers;
use tdt\input\scheduler\Schedule;


class InputResourceController extends \tdt\core\controllers\AController {

    /**
     * Only works with tdt/start
     */
    private function getDBConfig(){
        $db = array();
        $db["host"] = Config::get("db", "host");
        $db["name"] = Config::get("db", "name");
        $db["system"] = Config::get("db", "system");
        $db["user"] = Config::get("db", "user");
        $db["password"] = Config::get("db", "password");
        return $db;
    }
    

    public function GET($matches){
        if($this->isBasicAuthenticated()){
            $s = new Schedule($this->getDBConfig());
            if(isset($matches[1])){
                
            }
            
        }
    }
    
    public function PUT($matches){
        if($this->isBasicAuthenticated()){
            $s = new Schedule($this->getDBConfig());
        }
    }


    public function POST($matches){
        if($this->isBasicAuthenticated()){
            $s = new Schedule($this->getDBConfig());
        }
    }

    public function DELETE($matches){
        if($this->isBasicAuthenticated()){
            $s = new Schedule($this->getDBConfig());
        }
    }
}
