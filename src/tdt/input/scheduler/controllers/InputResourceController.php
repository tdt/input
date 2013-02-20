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
                $object->job= $s->getJob($matches["resource"]);
                if(empty($object->job)){
                    throw new TDTException("404",array($matches["resource"]));
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
            if(isset($matches["resource"])){
                $s = new Schedule($this->getDBConfig());
                //read all variables in one array
                $params = array();
                $HTTPheaders = getallheaders();
                if (isset($HTTPheaders["Content-Type"]) && $HTTPheaders["Content-Type"] == "application/json") {
                    $params = (array) json_decode(file_get_contents("php://input"));
                } else {
                    parse_str(file_get_contents("php://input"), $params);
                }
                
                $this->checkParams($params);
                $job = array();
                $job["config"] = $params;
                $job["name"] = $matches["resource"];
                $job["occurence"] = $params["occurence"];
                $s->add($job);
            }else{
                throw new TDTException(452,array("Please add the resourcename"));
            }
        }else{
            header('WWW-Authenticate: Basic realm="' . $this->hostname . $this->subdir . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
    }
    
    private function checkParams($params){
        //check whether the right parameters have been setup
        if(!isset($params["source"])){
            throw new TDTException(452,array("parameters source not set. Source is a url to a certain dataset that wants to be processed."));
        }

        if(!isset($params["occurence"])){
            throw new TDTException(452,array("parameters occurence not set. Occurence should the time in seconds in which this dataset needs to be recovered."));
        }
        if(!isset($params["extract"])){
            throw new TDTException(452,array("parameters extract not set. Extract is the name of the strategy to extract a file. For instance for a CSV file you would fill out: CSV"));
        }
        if(!isset($params["map"])){
            throw new TDTException(452,array("parameters map not set. Map is the name of the strategy to extract a file. For instance for mapping to RDF, you will need a mapfile and you will fill out RDF in this parameter."));
        }
        if(!isset($params["load"])){
            throw new TDTException(452,array("parameters load not set. Load is the name of the strategy to load the result in a store. For instance for loading it into a triple store, you will need to fill out: RDF"));
        }
        //TODO: more.
    }
    

    public function POST($matches){
        if($this->isBasicAuthenticated()){
            if(isset($matches["resource"])){
                throw new TDTException(452,array("Cannot post on a resource, maybe you wanted to use PUT?"));
            }else{
                $s = new Schedule($this->getDBConfig());                
                //read all variables in one array
                $params = $_POST;
                
                $this->checkParams($params);
                $job = array();
                $job["config"] = $params;
                if(!isset($params["name"])){
                    throw new TDTException(452,array("parameter name not set"));
                }

                if(! is_null($s->getJob($params["name"]))){
                    throw new TDTException(452,array("Job already exists"));
                }
                
                $job["name"] = $params["name"];
                $job["occurence"] = $params["occurence"];
                $s->add($job);
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
