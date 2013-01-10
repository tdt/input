<?php
/**
 * The Datacontroller retrieves requests to reload data in the datastore
 *
 */
class DataController extends AController {

    public function GET($matches){
        if(parent::isBasicAuthenticated()){
            $input = parse_ini_file("../custom/input.ini", true);
            //check if resource exists
            if(isset($input[$matches[1]])){
                $input = $input[$matches[1]];
            }else{
                throw new TDTException(404,array("data/" . $matches[1]));
            }
        }else{
            throw new TDTException(401,array());
        }
    }

    public static function processInput($config){
        Log::getInstance()->logInfo("processing input...");

    }
    

}
