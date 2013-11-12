<?php

namespace tdt\input\emlp\extract;

class XML extends AExtractor{

    private $next, $reader;
    private $index = 0;

    protected function open(){

        $uri = $this->extractor->uri;

        $this->reader = new \XMLReader();

        if(!$this->reader->open($uri)){
            $this->log("The uri ($uri) could not be opened. Make sure it is retrievable by the server.");
        }

        $arraylevel = $this->extractor->arraylevel;

        if(!$this->reader->next()){
            $this->log("Could not get next element from the XML document, make sure you provided the correct arraylevel.");
        }

        for($i = 1; $i < $arraylevel; $i++){
            $this->reader->read();
        }

        $this->next = $this->reader->expand();
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext(){

        if(!empty($this->next)){
            return true;
        }else{
            if($this->reader->next()){
                $this->next = $this->reader->expand();
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop(){

        if($this->hasNext()){

            $document = array();
            $this->makeFlat($document, $this->next);
            unset($this->next); // Delete it to clear memory for the next operation
            $this->index= 0;

            return $document;
        }else{
            return null;
        }

    }

    private function parseAttributes(&$document, &$xmlobject,$name){

        if(!empty($xmlobject->attributes)){

            foreach($xmlobject->attributes as $key => $value){

                $document[ $this->index ] = $value->value;
                $document[$name . "_attr_" . $key] = $value->value;
                $this->index++;
            }
        }
    }

    private function makeFlat(&$document, &$xmlobject, $parentname = ""){

        // Prefix for row names
        if($parentname == ""){

            $prefix = "";
            $name = $xmlobject->nodeName;
        }else{

            $prefix = $parentname;
            $name =  "_" . $xmlobject->nodeName;
        }

        // First the attributes
        $this->parseAttributes($document, $xmlobject , $prefix . $name);

        if(sizeof($xmlobject->childNodes) == 0){

            // Store the value of the element in the document array under its prefix name
            $document[ $prefix ] = $xmlobject->nodeValue;

            // Count the number of keys we have.
            $this->index++;
        }else{

            // Then the children
            $key_indices = array(); //an array of how many times a certain key occurred
            foreach($xmlobject->childNodes as $child){

                // If the child's name did not occur yet, add both [0] and without the 0 for backward compatibility
                if(!isset($key_indices[$child->nodeName])){

                    // Add a default key name without "[0]" for the first or only element
                    $this->makeFlat($document, $child, $prefix . $name);

                    // Add a [0] to this element as well for consistency
                    var_dump($document);

                    $document[$prefix . $name . "[0]"] = $document[$prefix . $name];

                    // Add a one in the occurence table
                    $key_indices[$child->nodeName] = 1;

                }else{

                    $this->makeFlat($document, $child, $prefix . $name . "[". $key_indices[$child->nodeName] ."]");
                    $key_indices[$child->nodeName]++;
                }
            }
        }
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close(){
        $this->reader->close();
    }
}