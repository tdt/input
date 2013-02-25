<?php

namespace tdt\input\scheduler\controllers;
use tdt\input\scheduler\Schedule;
use tdt\exceptions\TDTException;
use app\core\Config;
class Worker extends \tdt\core\controllers\AController {
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
            ignore_user_abort(true);
            set_time_limit(0);
            \tdt\core\utility\Config::setConfig(Config::getConfigArray());
            $s = new Schedule($this->getDBConfig());
            $s->execute();
        }else{
            header('WWW-Authenticate: Basic realm="' . $this->hostname . $this->subdir . '"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }
        
    }
}
