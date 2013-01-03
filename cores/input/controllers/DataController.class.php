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
            if(isset($input[$matches[0]])){
                $input = $input[$matches[0]];
//                ob_end_flush();
                var_dump($input);
//                ob_start();
//                ignore_user_abort(true);
//                set_time_limit(0);
            }else{
                throw new TDTException(404,array("data/" . $matches[0]));
            }
        }else{
            throw new TDTException(401,array());
        }
    }

}
