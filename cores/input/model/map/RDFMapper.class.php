<?php

class RDFMapper extends AMapper{

    public function execute(&$chunk){
        $graph = new EasyRdf_Graph();
        $baseurl = Config::get("general","hostname").Config::get("general","subdir");
        
        $thing = $graph->resource($baseurl . $chunk["0"], $this->config["row"]["class"]);
        $i = 0;
        foreach($this->config["columns"] as $index => $col){
            $thing->set($col,$chunk[$index]);
            $i++;
        }
        return $graph;
    }
}
