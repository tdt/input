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
use tdt\input\Input;
use tdt\core\formatters\FormatterFactory;
use tdt\exceptions\TDTException;
use app\core\Config;

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
            \tdt\core\utility\Config::setConfig(Config::getConfigArray());
            $format="";
            $object = new \stdClass();
            if(!empty($matches["format"])){
                $format= ltrim($matches["format"],'.');
            }
            $s = new Schedule($this->getDBConfig());
            if(isset($matches["resource"]) && $matches["resource"] != ""){
                $object->job = $s->getJob($matches["resource"]);
                unset($object->job["id"]);
                if(isset($object->job["map"])){
                    unset($object->job["map"]["id"]);
                    unset($object->job["map_id"]);
                }
                unset($object->job["load"]["id"]);
                unset($object->job["extract"]["id"]);

                unset($object->job["load_id"]);
                unset($object->job["extract_id"]);
                $object->job = array_filter($object->job);

                $object->job["extract"] = array_filter($object->job["extract"]);
                $object->job["map"] = array_filter($object->job["map"]);
                $object->job["load"] = array_filter($object->job["load"]);

                if(empty($object->job)){
                    throw new TDTException("404",array($matches["resource"]));
                }
                if(isset($matches["test"])){
                    //convert object to array
                    $job = json_decode(json_encode($object->job) ,true);
                    $input = new Input($job);
                    $input->execute();
                    exit();
                }
            }else{
                //when only TDTInput is requested, we will show all the jobs configured                
                $allnames = $s->getAllNames();
                foreach($allnames as $name){
                    $object->jobs[] = $this->hostname . $this->subdir . "TDTInput/" . $name["name"];   
                }
            }
            $f = new \tdt\formatters\Formatter(strtoupper($format));
            $f->execute("TDTInput",$object);
        }else{
            header('WWW-Authenticate: Basic realm="' . $this->hostname . $this->subdir . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
    }
    
    public function PUT($matches){
        if($this->isBasicAuthenticated()){
            if(!empty($matches["resource"])){
                $s = new Schedule($this->getDBConfig());
                //read all variables in one array 
                $params = json_decode(file_get_contents("php://input"));
                $params->name = $matches["resource"];
                //overwrite flag enabled: this has to be idempotent
                $s->add($params,true);
            }else{
                throw new TDTException(452,array("Please add the resourcename"));
            }
        }else{
            header('WWW-Authenticate: Basic realm="' . $this->hostname . $this->subdir . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
    }

    public function POST($matches){
        if($this->isBasicAuthenticated()){
            if(isset($matches["resource"])){
                throw new TDTException(452,array("Cannot post on a resource, maybe you wanted to use PUT?"));
            }else{
                $s = new Schedule($this->getDBConfig());
                //read all variables in one array
                $params = array();
                $params = json_decode(file_get_contents("php://input"));
                $s->add($params);
            }
        }else{
            header('WWW-Authenticate: Basic realm="' . $this->hostname . $this->subdir . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
    }

    public function DELETE($matches){
        if($this->isBasicAuthenticated()){
            $s = new Schedule($this->getDBConfig());
            if(isset($matches[1])){
                $s->delete($matches[1]);
            }
        }else{
            header('WWW-Authenticate: Basic realm="' . $this->hostname . $this->subdir . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
    }
}
