<?php
/**
 * Install a route towards POST tdtinput/test
 */
namespace tdt\input\scheduler\controllers;
use tdt\exceptions\TDTException;
use tdt\input\Input;
class TestController extends \tdt\core\controllers\AController {    

    /**
     * POST should contain a json which includes 3 elements:
     *  * chunk (a chunk in a certain format)
     *  * extract (an extractor recipe)
     *  * mapping (a Vertere mapping configuration)
     */
    public function POST($matches){
        //loads all input in an array
        $paramsjson = file_get_contents("php://input");
        $params = json_decode($paramsjson,true);
        //start checking the parameters
        if(!isset($params["chunk"]) || empty($params["chunk"])){
            throw new TDTException(452, array("The chunk you want to map is empty or not set."));
        }

        if(!isset($params["extract"]) || empty($params["extract"])){
            throw new TDTException(452, array("The extract configuration is not set or empty."));
        }

        if(!isset($params["mapping"]) || empty($params["mapping"])){
            throw new TDTException(452, array("The mapping configuration is not set or empty."));
        }
        $extract = $params["extract"];
        if($extract["type"] === "JSON"){
            $params["chunk"] = "[" . $params["chunk"]. "]";
        }

        $urlsrc = tempnam("","tdt");
        $srchandle = fopen($urlsrc, "w");
        fwrite($srchandle,$params["chunk"]);
        
        $mapfile = tempnam("","tdt");
        $maphandle = fopen($mapfile, "w");
        fwrite($maphandle,$params["mapping"]);
        fclose($maphandle);
        
        $extract["source"] = "file://" . $urlsrc;
        $config = array(
            "extract" => $extract,
            "map" => array(
                "type" => "RDF",
                "mapfile" => "file://" . $mapfile,
                "datatank_uri" => "http://example.com/",
                "datatank_package"=> "foo", 
                "datatank_resource" => "bar"
            ),
            "load" => array(
                "type" => "CLI"
            )
        );
        try{
            $input = new Input($config);
            $input->execute();
        }catch(Exception $e){
            set_error_header(400, "Client error");
            echo "There is an error in your configuration: " . $e->getMessage();
        }
        
        unlink($mapfile);
        unlink($urlsrc);
        
    }
}
