<?php
namespace tdt\input\extract;

define('EASYRDF_DIR', __DIR__ . '/../../../../includes/easyrdf/lib/');
define('VERTERE_DIR', __DIR__ . '/../../../../includes/Vertere/dist/');
define('MORIARTY_DIR',  __DIR__ . '/../../../../includes/Vertere/dist/lib/moriarty/');
define('MORIARTY_ARC_DIR', __DIR__ . '/../../../../includes/Vertere/dist/lib/arc/');

include_once EASYRDF_DIR . 'EasyRdf.php';
include_once MORIARTY_DIR . 'moriarty.inc.php';
include_once MORIARTY_DIR . 'simplegraph.class.php';
include_once VERTERE_DIR . 'inc/sequencegraph.class.php';
include_once VERTERE_DIR . 'inc/vertere.class.php';
include_once VERTERE_DIR . 'inc/diagnostics.php';

class RDFa extends \tdt\input\AExtractor{

    private $handle, $graph, $next;

    protected function open($url){
        $this->handle = fopen('php://temp', 'w+');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FILE, $this->handle);
        curl_exec($curl);
        curl_close($curl);
        rewind($this->handle);

        $this->graph = new \EasyRdf_Graph();
        $this->graph->load($url, 'rdfa');

        $this->graph_array = $this->graph->toArray();
        $this->next = current($this->graph_array);
    }
    
    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext(){

        return ($this->next ? true : false);
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop(){
       
        $this->graph2 = new \SimpleGraph();

        $key = key($this->graph_array);
        //var_dump($key);
        $this->graph2->_index[$key] = current($this->graph_array);
        //var_dump($this->graph2->_index);
        
        if(empty($this->graph2->_index)){
            $chunk_string = implode(",", $chunk);
            $this->log[] = "The created graph was empty, chunk contained the following information: $chunk_string";
        }

        $this->next = next($this->graph_array);

        return $this->graph2;
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close(){
        fclose($this->handle);
    }

}
