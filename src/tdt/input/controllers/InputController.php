<?php

namespace tdt\input\controllers;

class InputController extends \Controller{

    public function handle(){

        // Propage the request based on the HTTPMethod of the request.
        $method = \Request::getMethod();
        var_dump($method);

        switch($method){
            case "PUT":
                //return self::createDefinition($uri);
                break;
            case "GET":
                //return self::getDefinition($uri);
                break;
            case "PATCH":
                //return self::patchDefinition($uri);
                break;
            case "DELETE":
                //return self::deleteDefinition($uri);
                break;
            case "HEAD":
                //return self::headDefinition($uri);
                break;
            default:
                \App::abort(400, "The method $method is not supported by the definitions.");
                break;
        }

        exit();
    }
}