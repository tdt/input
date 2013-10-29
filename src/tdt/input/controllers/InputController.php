<?php

namespace tdt\input\controllers;
use tdt\core\ContentNegotiator;

class InputController extends \Controller{

    public function handle(){

        // Propage the request based on the HTTPMethod of the request.
        $method = \Request::getMethod();

        switch($method){
            case "PUT":
                return self::createJob();
                break;
            case "GET":
                return self::getJob();
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
                \App::abort(400, "The method $method is not supported by the jobs.");
                break;
        }

        exit();
    }

     /**
     * Create a new job based on the PUT parameters given and content-type.
     */
    private static function createJob(){

        $uri = \Request::path();
        $uri = str_replace('input/', '', $uri);

        // Check if the uri already exists
        if(self::exists($uri)){
            \App::abort(452, "This uri already exists, use POST if you wanted to update the job.");
        }

        list($collection_uri, $name) = self::getParts($uri);

        // Retrieve the collection uri and resource name
        $matches = array();

        // Retrieve the parameters of the PUT requests (either a JSON document or a key=value string)
        $params = \Request::getContent();

        // Is the body passed as JSON, if not try getting the request parameters from the uri
        if(!empty($params)){
            $params = json_decode($params, true);
        }

        // If we get empty params, then something went wrong
        if(empty($params)){
            \App::abort(452, "The parameters could not be parsed from the body or request URI, make sure parameters are provided and if they are correct (e.g. correct JSON).");
        }

        // Check which parts are set for validation purposes
        $extract = @$params['extract'];
        $map = @$params['map'];
        $load = @$params['load'];
        $publisher = @$params['publish'];

        // Check for every emlp part if the type is supported
        $extractor = self::validateType(@$extract, 'extract');
        $mapper = self::validateType(@$map, 'map');
        $loader = self::validateType(@$load, 'load');
        $publisher = self::validateType(@$publisher, 'publish');

        // Save the emlp models
        $extractor->save();
        $loader->save();

        if(!empty($mapper)){
            $mapper->save();
        }

        if(!empty($publisher)){
            $publisher->save();
        }

        // Create the job associated with emlp relations
        $job = new \Job();
        $job->collection_uri = $collection_uri;
        $job->name = $name;
        $job->extractor_id = $extractor->id;
        $job->extractor_type = self::getClass($extractor);
        $job->mapper_id = @$mapper->id;
        $job->mapper_type = self::getClass($mapper);
        $job->loader_id = $loader->id;
        $job->loader_type = self::getClass($loader);
        $job->publisher_id = @$publisher->id;
        $job->publisher_type = self::getClass($publisher);
        $job->save();

        $response = \Response::make(null, 200);
        $response->header('Location', \Request::getHost() . '/' . $uri);

        return $response;
    }

    /**
     * Check if a given type of the emlp exists.
     */
    private static function validateType($params, $ns){

        $type = @$params['type'];
        $type = ucfirst(mb_strtolower($type));

        // Map and publish are not obligatory
        if(empty($type)){
            if($ns != 'map' && $ns != 'publish'){
                \App::abort(452, "No type of $ns was given, please provide a type of $ns.");
            }else{
                return;
            }
        }

        $class_name = $ns . "\\" . $type;

        if(!class_exists($class_name)){
            \App::abort(452, "The given type ($type) is not a $ns type.");
        }

        $class = new $class_name();

        // Validate the properties of the given type
        $validated_params = self::validateParameters($class, $params);

        foreach($validated_params as $key => $value){
            $class->$key = $value;
        }

        return $class;
    }

    /**
     * Validate the create parameters based on the rules of a certain job.
     * If something goes wrong, abort the application and return a corresponding error message.
     */
    private static function validateParameters($type, $params){

        $validated_params = array();

        $create_params = $type::getCreateProperties();
        $rules = $type::getCreateValidators();

        foreach($create_params as $key => $info){

            if(!array_key_exists($key, $params)){

                if(!empty($info['required']) && $info['required']){
                    $type = explode('\\', $type);
                    $type = array_shift($type);
                    \App::abort(452, "The parameter $key is required in order to create a the $type part of the job but was not provided.");
                }

                $validated_params[$key] = @$info['default_value'];

            }else{

                if(!empty($rules[$key])){

                    $validator = \Validator::make(
                        array($key => $params[$key]),
                        array($key => $rules[$key])
                    );

                    if($validator->fails()){
                        \App::abort(452, "The validation failed for parameter $key, make sure the value is valid.");
                    }
                }

                $validated_params[$key] = $params[$key];
            }
        }

        return $validated_params;
    }

    /**
     * Delete a job based on the URI given.
     */
    private static function deleteJob($uri){

        list($collection_uri, $name) = self::getParts($uri);

        $job = self::get($uri);

        if(empty($job)){
            \App::abort(452, "The given uri, $uri, could not be resolved as a resource that can be deleted.");
        }

        $job->delete();

        $response = \Response::make(null, 200);
        return $response;
    }

    /**
     * PATCH a job based on the PATCH parameters and URI.
     */
    private static function patchJob($uri){

    }

    /**
     * Return the headers of a call made to the uri given.
     */
    private static function headJob($uri){

    }
    /*
     * GET a job based on the uri provided
     * TODO add support function get retrieve collections, instead full resources.
     */
    private static function getJob(){

        $uri = \Request::path();
        var_dump($uri);

        // TODO give back all the collections

        if(!self::exists($uri)){
            \App::abort(452, "No job has been found with the uri $uri");
        }

        // Get Definition object based on the given uri
        $job = self::get($uri);

        $job_properties = $job->getAllProperties();

        // Return properties document in JSON
        // TODO return this in more formats?
        return str_replace("\/", "/", json_encode($job_properties));

    }

    /**
     * Get a job object with the given uri.
     */
    public static function get($uri){
        return \Job::whereRaw("? like CONCAT(collection_uri, '/', name , '/', '%')", array($uri . '/'))->first();
    }

    /**
     * Check if a resource exists with a given uri.
     */
    public static function exists($uri){
        $job = self::get($uri);
        return !empty($job);
    }

    /**
     * Return the collection uri and resource (if it exists)
     */
    private static function getParts($uri){

        if(preg_match('/(.*)\/([^\/]*)$/', $uri, $matches)){
            $collection_uri = $matches[1];
            $name = @$matches[2];
        }else{
            \App::abort(452, "The uri should at least have a collection uri and a resource name.");
        }

        return array($collection_uri, $name);
    }

    /**
     * Get the class without the namespace
     */
    private static function getClass($obj){

        if(is_null($obj)){
            return null;
        }

        $class = explode('\\', get_class($obj));
        return ucfirst(mb_strtolower(end($class)));
    }
}