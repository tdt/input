<?php
namespace tdt\input\extract;
use \Exception;
use \XMLReader;

class XML extends \tdt\input\AExtractor{

    private $next, $reader;

    protected function open($url){
        $this->reader = new XMLReader();
        if(!$this->reader->open($url)){
            throw new Exception("$url could not be opened. Are you sure the URL is correct?");
        }
        $arraylevel=$this->config["arraylevel"];
        if(!$this->reader->next()){
            throw new Exception("could not get next element");
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
            unset($this->next); //delete it to clear memory for the next operation
            $this->index= 0;
            return $document;
        }else{
            throw new Exception("Please check if we have a next item before popping");
        }
        
    }

    private $index = 0;

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
        //prefix for row names
        if($parentname == ""){
            $prefix = "";
            $name = $xmlobject->nodeName;
        }else{
            $prefix = $parentname;
            $name =  "_" . $xmlobject->nodeName;
        }
        
        //first the attributes
        $this->parseAttributes($document, $xmlobject , $prefix . $name);

        if(sizeof($xmlobject->childNodes) == 0){
            // $prefix → Apparently we have to use numbers, added $prefix for clarity
            $document[ $this->index ] = $xmlobject->nodeValue;
            $document[ $prefix ] = $xmlobject->nodeValue;
            $this->index++;
        }else{
            //then the children
            foreach($xmlobject->childNodes as $child){   
                $this->makeFlat($document, $child, $prefix . $name);
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
