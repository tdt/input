<?php

namespace tdt\input\emlp\helper;

class JSONInputProcessor implements \tdt\json\JSONChunkProcessor{

    private $obj;
    private $new = false;

    public function process($chunk){

        // Set the flag: a new object is loaded
        $this->new = true;
        $this->obj = $this->flatten(json_decode($chunk, true));
    }

    /**
     * Flatten the hierarchical data
     */
    private function flatten($ar){

        $new = array();

        foreach($ar as $k => $v) {
            if(is_array($v)){
                $prefix = $k;
                $flat = $this->flatten($v);
                foreach($flat as $fkey => $fval){
                    $new[$prefix . "_" . $fkey] = $fval;
                }
            }else{
                $new[$k] = $v;
            }
        }

        return $new;
    }

    public function hasNew(){
        return $this->new;
    }

    /**
     * Return a new chunk of data
     */
    public function pop(){
        if($this->new){
            $this->new = false;
            return $this->obj;
        }else{
            return null;
        }
    }
}