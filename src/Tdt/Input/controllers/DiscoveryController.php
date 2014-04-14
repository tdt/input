<?php

namespace Tdt\Input\Controllers;

/**
 * Controller that helps building the discovery document.
 */
class DiscoveryController extends \Controller
{

    public static function createDiscoveryDocument()
    {

        // Create and return a document that holds a self-explanatory document
        // about how to interface with the datatank
        // This document only starts with methods, not resources for it is used
        // as part of the aggregated discovery document in core.
        $discovery_document = new \stdClass();

        $methods = new \stdClass();

        // Attach the methods to the up the methods object
        $methods->get = self::createGetDocumentation();
        $methods->put = self::createPutDocumentation();
        $methods->delete = self::createDeleteDocumentation();
        //$methods->patch = self::createPatchDocumentation();

        // Attach the methods to the input discovery object
        $discovery_document->methods = $methods;

        return $discovery_document;
    }

    /**
     * Create the get discovery documentation.
     */
    private static function createGetDocumentation()
    {

        $get = new \stdClass();

        $get->httpMethod = "GET";
        $get->path = "/input/{identifier}";
        $get->description = "Get a job identified by the {identifier} value.";

        return $get;
    }

    /**
     * Create the put discovery documentation.
     */
    private static function createPutDocumentation()
    {

        $put = new \stdClass();

        $put->httpMethod = "PUT";
        $put->path = "/input/{identifier}";
        $put->description = "Create a new input job that consists of an extract, mapping (optional), loading and publishing (optional) process. The {identifier} identifies the configuration.";

        // We need to create a hierarchical set of parameters as the emlp have different options as well

        $parameters = new \stdClass();

        $parameters = \Job::getCreateProperties();

        // Add the extract options

        $extract = new \stdClass();
        $type_param = array('{extract_type}' => array('required' => true, 'description' => 'Defines the datastructure of which data will be extracted.'));

        $extract_types = array();

        // Fetch all the supported extract models by iterating the models/extract directory
        if ($handle = opendir(__DIR__ . '/../../../models/extract')) {
            while (false !== ($entry = readdir($handle))) {

                // Skip the . and .. directory
                if (preg_match("/(.+)\.php/", $entry, $matches)) {

                    $model = 'extract\\' . $matches[1];
                    $type = strtolower($matches[1]);


                    if (method_exists($model, 'getCreateProperties')) {

                        $extract_types[$type] = new \stdClass();
                        $extract_types[$type]->parameters = $model::getCreateProperties();
                    }
                }
            }
            closedir($handle);
        }

        $extract->parameters['type'] = $extract_types;
        $parameters['extract'] = $extract;

        // Add the mapping options

        $map = new \stdClass();
        $type_param = array('{map_type}' => array('required' => true, 'description' => 'Defines the datastructure of which data will be maped.'));

        $map_types = array();

        // Fetch all the supported map models by iterating the models/map directory
        if ($handle = opendir(__DIR__ . '/../../../models/map')) {
            while (false !== ($entry = readdir($handle))) {

                // Skip the . and .. directory
                if (preg_match("/(.+)\.php/", $entry, $matches)) {

                    $model = 'map\\' . $matches[1];
                    $type = strtolower($matches[1]);


                    if (method_exists($model, 'getCreateProperties')) {

                        $map_types[$type] = new \stdClass();
                        $map_types[$type]->parameters = $model::getCreateProperties();
                    }
                }
            }
            closedir($handle);
        }

        $map->parameters['type'] = $map_types;
        $parameters['map'] = $map;

        // Add the loading options

        $load = new \stdClass();
        $type_param = array('{load_type}' => array('required' => true, 'description' => 'Defines the datastructure of which data will be loaded.'));

        $load_types = array();

        // Fetch all the supported load models by iterating the models/load directory
        if ($handle = opendir(__DIR__ . '/../../../models/load')) {
            while (false !== ($entry = readdir($handle))) {

                // Skip the . and .. directory
                if (preg_match("/(.+)\.php/", $entry, $matches)) {

                    $model = 'load\\' . $matches[1];
                    $type = strtolower($matches[1]);


                    if (method_exists($model, 'getCreateProperties')) {

                        $load_types[$type] = new \stdClass();
                        $load_types[$type]->parameters = $model::getCreateProperties();
                    }
                }
            }
            closedir($handle);
        }

        $load->parameters['type'] = $load_types;
        $parameters['load'] = $load;

        // Add the publishing options
        // TODO

        $put->body = $parameters;

        return $put;
    }

    /**
     * Create the delete discovery documentation.
     */
    private static function createDeleteDocumentation()
    {

        $delete = new \stdClass();

        $delete->httpMethod = "DELETE";
        $delete->path = "/input/{identifier}";
        $delete->description = "Delete a job identified by the {identifier} value.";

        return $delete;
    }
}
