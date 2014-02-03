<?php

namespace tdt\input\emlp\helper\json;

/**
 * This class catches events from the JSON Parser class.
 * This processor only returns chunks when an array is found in the document.
 * Every array entry will be returned using the pop() function.
 */

class JsonProcessor implements Listener{

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
        // Do nothing
    }

    public function end_document(){
        // Do nothing
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

    /**
     * Treat an array the same way as an object
     */
    public function start_array(){

        if(!$this->array_start){
            $this->array_start = true;
        }

        // TODO this for hierchical chunks
        //$this->start_object();
    }


    /**
     * Treat an array the same was an object
     */
    public function end_array(){
        $this->end_object();
    }

    /**
     * The key is a JSON key, will always be a string
     */
    public function key($key){
        $this->key = $key;
    }

    /**
     * JSON value, may be a string, integer, boolean, array, etc.
     * TODO flatten hierarchical values
     */
    public function value($value){
        $key = $this->key;
        $this->chunk[$key] = $value;
    }

    /**
     * If a new entry was fully created (streamingly) from the array
     * return true, if not false.
     */
    public function hasNew(){
        return $this->new_chunk;
    }

    /**
     * Return the streamingly created array chunk and return it
     */
    public function pop(){

        $chunk = $this->chunk;
        $this->new_chunk = false;
        $this->chunk = array();
        return $chunk;
    }
}
