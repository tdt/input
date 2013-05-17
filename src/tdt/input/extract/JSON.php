<?php
namespace tdt\input\extract;

class JSON extends \tdt\input\AExtractor{

    private $handle, $reader, $processor;

    protected function open($url){
        $this->processor = new JSONInputProcessor();
        $this->reader = new \tdt\json\JSONCharInputReader($this->processor);
        $this->handle = fopen('php://temp', 'w+');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FILE, $this->handle);
        curl_exec($curl);
        curl_close($curl);
        rewind($this->handle);
    }
    
    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext(){
        return !feof($this->handle);
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop(){
        while(!$this->processor->hasNew() && !feof($this->handle)){
            $char = fread($this->handle, 1);
            if($char && $char != "\n"){
                $this->reader->readChar($char);
            }
        }
        if($this->processor->hasNew()){
            return $this->processor->pop();
        }else{
            return FALSE;
        }
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close(){
        fclose($this->handle);
    }

}

class JSONInputProcessor implements \tdt\json\JSONChunkProcessor{
    private $obj;
    private $new = false;

    public function process($chunk){
        //set the flag: a new object is loaded
        $this->new = true;
        $this->obj = json_decode($chunk, true);
    }

    public function hasNew(){
        return $this->new;
    }

    public function pop(){
        if($this->new){
            $this->new = false;
            return $this->obj;
        }else{
            return FALSE;
        }
    }
    
}
