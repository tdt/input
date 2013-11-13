<?php

namespace tdt\input\emlp\helper\json;

class JsonProcessor implements Listener{

    // We only read data from the first array occurence
    private $array_start;
    private $key;
    private $chunk;
    private $new_chunk;

    // Keep track of the depth of each entry in the array
    // If it's 0 and the object ends, a new json object has been constructed
    private $depth;

    public function __construct(){
        $this->array_start = false;
        $this->depth = 0;
        $this->new_chunk = false;
        $this->chunk = array();
    }

    public function start_document(){

    }

    public function end_document(){

    }

    public function start_object(){
        if($this->array_start){
            $this->new_chunk = false;
            $this->depth++;
        }
    }

    public function end_object(){
        if($this->array_start){
            $this->depth--;
            if($this->depth == 0){
                $this->new_chunk = true;
            }
        }
    }

    public function start_array(){

        if(!$this->array_start){
            $this->array_start = true;
        }
        // Do this for hierchical chunks
        //$this->start_object();
    }

    public function end_array(){
        $this->end_object();
    }

    // Key will always be a string
    public function key($key){
        $this->key = $key;
    }

    // Note that value may be a string, integer, boolean, array, etc.
    public function value($value){
        $key = $this->key;
        $this->chunk[$key] = $value;
    }

    public function hasNew(){
        return $this->new_chunk;
    }

    public function pop(){

        $chunk = $this->chunk;
        $this->new_chunk = false;
        $this->chunk = array();
        return $chunk;
    }
}
