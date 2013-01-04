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

//                ignore_user_abort(true);
//                set_time_limit(0);
//                ob_end_flush();
                register_shutdown_function("DataController::processInput",$input);
//                ob_start();

                //shut down the server, so the shutdown function will be called
                exit();
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
