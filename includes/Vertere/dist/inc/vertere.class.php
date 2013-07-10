<?php

include_once MORIARTY_DIR . 'moriarty.inc.php';
include_once MORIARTY_DIR . 'simplegraph.class.php';
include_once VERTERE_DIR . 'custom/conversions.class.php';
include_once 'UriTemplate/UriTemplate.php';

class Vertere {

    private $spec, $spec_uri, $resources, $base_uri, $lookups = array(), $null_values = array(), $header;

    public function __construct($spec, $spec_uri) {
        $this->spec = $spec;
        $this->spec_uri = $spec_uri;

        // Find resource specs
        $this->resources = $spec->get_resource_triple_values($this->spec_uri, NS_CONV . 'resource');
        if (empty($this->resources)) {
            throw new Exception('Unable to find any resource specs to work from');
        }

        $this->base_uri = $spec->get_first_literal($this->spec_uri, NS_CONV . 'base_uri');

        // :null_values is a list of strings that indicate NULL in the source data
        $null_value_list = $spec->get_first_resource($this->spec_uri, NS_CONV . 'null_values');
        if ($null_value_list) {
            foreach ($spec->get_list_values($null_value_list) as $null_value_resource) {
                if ($null_value_resource["type"] == "literal") {
                    array_push($this->null_values, $null_value_resource["value"]);
                }
            }
        } else {
            array_push($this->null_values, "");
        }
        foreach ($this->null_values as $value) {
            
        }
    }

    /*
     * Method to support named columns (MVS)
     */

    public function get_record_value($record, $source_column) {
        $key = array_search($source_column, $this->header);
        if ($key === false) {
            if (is_numeric($source_column))
                $source_column--;
            else if (!is_string($source_column))
                throw new Exception("Source column value is not valid: string or numeric");

            if (array_key_exists($source_column, $record))
                return trim($record[$source_column]);

            //echo "Column reference $source_column is not found in source\n";
            return;
        }

        if (!array_key_exists($key, $record))
            throw new Exception("Source column value is not valid");

        return trim($record[$key]);
    }

    public function record_key_exists($source_column, $record) {
        $key = array_search($source_column, $this->header);
        if ($key === false) {
            if (is_numeric($source_column))
                $source_column--;
            else if (!is_string($source_column))
                throw new Exception("Source column value is not valid: string or numeric");

            return array_key_exists($source_column, $record);
        }

        return !array_key_exists($key, $record);
    }

    public function convert_array_to_graph($record, $header = array()) {
        if (!is_array($header))
            throw new Exception("Supplied header is no array!");

        $this->header = $header;

        $uris = $this->create_uris($record);
        $graph = new SimpleGraph();
        $this->add_default_types($graph, $uris);
        $this->create_relationships($graph, $uris, $record);
        $this->create_attributes($graph, $uris, $record);
        return $graph;
    }

    private function add_default_types($graph, $uris) {
        foreach ($this->resources as $resource) {
            $types = $this->spec->get_resource_triple_values($resource, NS_CONV . 'type');
            foreach ($types as $type) {
                if (!empty($type) && isset($uris[$resource])) {
                    $graph->add_resource_triple($uris[$resource], NS_RDF . 'type', $type);
                }
            }
        }
    }

    private function create_attributes(&$graph, $uris, $record) {
        foreach ($this->resources as $resource) {
            $attributes = $this->spec->get_resource_triple_values($resource, NS_CONV . 'attribute');
            foreach ($attributes as $attribute) {
                $this->create_attribute($graph, $uris, $record, $resource, $attribute);
            }
        }
    }

    private function create_attribute(&$graph, $uris, $record, $resource, $attribute) {
        if (!isset($uris[$resource])) {
            return;
        }
        $subject = $uris[$resource];
        $property = $this->spec->get_first_resource($attribute, NS_CONV . 'property');
        $language = $this->spec->get_first_literal($attribute, NS_CONV . 'language');
        $datatype = $this->spec->get_first_resource($attribute, NS_CONV . 'datatype');

        $value = $this->spec->get_first_literal($attribute, NS_CONV . 'value');
        $source_column = $this->spec->get_first_literal($attribute, NS_CONV . 'source_column');
        $source_columns = $this->spec->get_first_resource($attribute, NS_CONV . 'source_columns');

        if ($value) {
            $source_value = $value;
        } else if ($source_column) {
//            $source_column--;
//            $source_value = $record[$source_column];
            $source_value = $this->get_record_value($record, $source_column);
        } else if ($source_columns) {
            $source_columns = $this->spec->get_list_values($source_columns);
            $glue = $this->spec->get_first_literal($attribute, NS_CONV . 'source_column_glue');
            $filter = $this->spec->get_first_literal($attribute, NS_CONV . 'source_column_filter');
            if (!isset($filter)) {
                // default: accept anything
                $filter = "//";
            }
            $source_values = array();
            foreach ($source_columns as $source_column) {
                $source_column = $source_column['value'];

//                $source_column--;
//                $value = $record[$source_column];
                $value = $this->get_record_value($record, $source_column);

                if (preg_match($filter, $value) != 0 && !in_array($value, $this->null_values)) {
                    $source_values[] = $value;
                }
            }
            $source_value = implode($glue, $source_values);
        } else {
            return;
        }
        $lookup = $this->spec->get_first_resource($attribute, NS_CONV . 'lookup');
        if ($lookup != null) {
            $lookup_value = $this->lookup($source_column, $record, $lookup, $source_value);
            if ($lookup_value != null && $lookup_value['type'] == 'uri') {
                $graph->add_resource_triple($subject, $property, $lookup_value['value']);
                return;
            } else {
                $source_value = $lookup_value['value'];
            }
        }

        if (empty($source_value)) {
            return;
        }

        $source_value = $this->process($attribute, $source_value);
        $graph->add_literal_triple($subject, $property, $source_value, $language, $datatype);
    }

    private function create_relationships(&$graph, $uris, $record) {
        foreach ($this->resources as $resource) {
            $relationships = $this->spec->get_resource_triple_values($resource, NS_CONV . 'relationship');
            foreach ($relationships as $relationship) {
                $this->create_relationship($graph, $uris, $resource, $relationship, $record);
            }
        }
    }

    private function create_relationship(&$graph, $uris, $resource, $relationship, $record) {
        $subject = null;
        if (array_key_exists($resource, $uris))
            $subject = $uris[$resource];

        $property = $this->spec->get_first_resource($relationship, NS_CONV . 'property');

        $object_from = $this->spec->get_first_resource($relationship, NS_CONV . 'object_from');
        $identity = $this->spec->get_first_resource($relationship, NS_CONV . 'identity');
        $object = $this->spec->get_first_resource($relationship, NS_CONV . 'object');
        $new_subject = $this->spec->get_first_resource($relationship, NS_CONV . 'subject');

        if ($object_from) {
            //Prevents PHP warning on key not being present  
            if (isset($uris[$object_from]))
                $object = $uris[$object_from];
        } else if ($identity) {
            // we create a link in situ, from a colum value
            // TODO: this should be merged with the create_uri() code
            $source_column = $this->spec->get_first_literal($identity, NS_CONV . 'source_column');
//            $source_column--;
//            $source_value = $record[$source_column];
            $source_value = $this->get_record_value($record, $source_column);

            if (empty($source_value)) {
                return;
            }

            //Check for lookups
            $lookup = $this->spec->get_first_resource($identity, NS_CONV . 'lookup');
            if ($lookup != null) {
                $lookup_value = $this->lookup($lookup, $source_value);
                if ($lookup_value != null && $lookup_value['type'] == 'uri') {
                    $uris[$resource] = $lookup_value['value'];
                    return;
                } else {
                    $source_value = $lookup_value['value'];
                }
            }

            $base_uri = $this->spec->get_first_literal($identity, NS_CONV . 'base_uri');
            if ($base_uri === null) {
                $base_uri = $this->base_uri;
            }
            $source_value = $this->process($identity, $source_value);
            $object = "${base_uri}${source_value}";
        } else if ($new_subject) {
            $object = $subject;
            $subject = $new_subject;
        }

        if ($subject && $property && $object) {
            $graph->add_resource_triple($subject, $property, $object);
        } else {
            return;
        }
    }

    private function create_uris($record) {
        $uris = array();
        foreach ($this->resources as $resource) {
            if (!isset($uris[$resource])) {
                $this->create_uri($record, $uris, $resource);
            }
        }
        return $uris;
    }

    private function create_template_uri($record, $template, $vars) {
        $var_arr = array();
        foreach ($vars as $var) {
            $name = $this->spec->get_first_literal($var, NS_CONV . 'variable');
            $source_column = $this->spec->get_first_literal($var, NS_CONV . 'source_column');
            $value = $this->get_record_value($record, $source_column);
            $var_arr[$name] = $value;
        }

        $processor = new \Guzzle\Parser\UriTemplate\UriTemplate();
        return $processor->expand($template, $var_arr);
    }

    private function create_uri($record, &$uris, $resource, $identity = null) {
        if (!$identity) {
            $identity = $this->spec->get_first_resource($resource, NS_CONV . 'identity');
        }
        $source_column = $this->spec->get_first_literal($identity, NS_CONV . 'source_column');
        $source_columns = $this->spec->get_first_resource($identity, NS_CONV . 'source_columns');
        $source_resource = $this->spec->get_first_resource($identity, NS_CONV . 'source_resource');
        //Support for URI templates
        $template = $this->spec->get_first_literal($identity, NS_CONV . 'template');


        if ($template) {
            //Retrieve all declared variables and expand template
            //For now, only an unprocessed single column value is supported as a template variable
            //Future: support source_columns, source_resource, lookup and process as well => refactor whole method
            $vars = $this->spec->get_resource_triple_values($identity, NS_CONV . 'template_vars');
            $uri = $this->create_template_uri($record, $template, $vars);
            $uris[$resource] = $uri;
            return;
        } else if ($source_column) {
            $source_value = $this->get_record_value($record, $source_column);
        } else if ($source_columns) {
            $source_columns = $this->spec->get_list_values($source_columns);
            $glue = $this->spec->get_first_literal($identity, NS_CONV . 'source_column_glue');
            $source_values = array();

            foreach ($source_columns as $source_column) {
                $source_column = $source_column['value'];
                //$source_column--;
                //Check if the decremented index exists before using its value 
                $key = is_numeric($source_column) ? $source_column - 1 : $source_column;

                if (array_key_exists($key, $record)) {
                    // if (!empty($record[$source_column])) {  // empty() is not a good idea: empty(0) == TRUE
                    if (!in_array($record[$key], $this->null_values)) {
                        //$source_values[] = $record[$source_column];
                        $source_values[] = $this->get_record_value($record, $source_column);
                    } else {
                        $source_values = array();
                        break;
                    }
                }
            }

            $source_value = implode('', $source_values);
            if (!empty($source_value)) {
                $source_value = implode($glue, $source_values);
            }
        } else if ($source_resource) {
            if (!isset($uris[$source_resource])) {
                $this->create_uri($record, $uris, $source_resource);
            }
            //Prevents PHP warning on key not being present   
            if (isset($uris[$source_resource]))
                $source_value = $uris[$source_resource];
        } else {
            return;
        }

        //Check for lookups
        $lookup = $this->spec->get_first_resource($identity, NS_CONV . 'lookup');
        if ($lookup != null) {
            $lookup_value = $this->lookup($source_value, $record, $lookup, $source_value);
            if ($lookup_value != null && $lookup_value['type'] == 'uri') {
                $uris[$resource] = $lookup_value['value'];
                return;
            } else {
                $source_value = $lookup_value['value'];
            }
        }

        //Decide on base_uri
        $base_uri = $this->spec->get_first_literal($identity, NS_CONV . 'base_uri');
        if ($base_uri === null) {
            $base_uri = $this->base_uri;
        }

        //Decide if the resource should be nested (overrides the base_uri)
        $nest_under = $this->spec->get_first_resource($identity, NS_CONV . 'nest_under');
        if ($nest_under != null) {
            if (!isset($uris[$nest_under])) {
                $this->create_uri($record, $uris, $nest_under);
            }
            $base_uri = $uris[$nest_under];
            if (!preg_match('%[/#]$%', $base_uri)) {
                $base_uri .= '/';
            }
        }

        $container = $this->spec->get_first_literal($identity, NS_CONV . 'container');
        if (!empty($container) && !preg_match('%[/#]$%', $container)) {
            $container .= '/';
        }

        //Prevents PHP warning on key not being present  
        if (!isset($source_value))
            $source_value = null;

        $source_value = $this->process($identity, $source_value);

        if (!empty($source_value)) {
            $uri = "${base_uri}${container}${source_value}";
            $uris[$resource] = $uri;
        } else {
            $identity = $this->spec->get_first_resource($resource, NS_CONV . 'alternative_identity');
            if ($identity) {
                $this->create_uri($record, $uris, $resource, $identity);
            }
        }
    }

    public function process($resource, $value) {
        $processes = $this->spec->get_first_resource($resource, NS_CONV . 'process');
        if ($processes != null) {
            $process_steps = $this->spec->get_list_values($processes);
            foreach ($process_steps as $step) {
                $function = str_replace(NS_CONV, "", $step['value']);
                switch ($function) {
                    case 'normalise':
                        //$value = strtolower(str_replace(' ', '_', trim($value)));
                        // Swap out Non "Letters" with a _
                        $value = preg_replace('/[^\\pL\d]+/u', '_', $value);

                        // Trim out extra -'s
                        $value = trim($value, '-');

                        // Convert letters that we have left to the closest ASCII representation
                        $value = iconv('utf-8', 'us-ascii//TRANSLIT', $value);

                        // Make text lowercase
                        $value = strtolower($value);

                        // Strip out anything we haven't been able to convert
                        $value = preg_replace('/[^-\w]+/', '', $value);

                        break;

                    case 'trim_quotes':
                        $value = trim($value, '"');
                        break;

                    case 'flatten_utf8':
                        $value = preg_replace('/[^-\w]+/', '', iconv('UTF-8', 'ascii//TRANSLIT', $value));
                        break;

                    case 'title_case':
                        $value = ucwords($value);
                        break;

                    case 'url_encode':
                        $value = urlencode($value);
                        $value = str_replace("+", "%20", $value);
                        break;

                    /**
                     * create_url wil check whether the argument is not a url yet. 
                     * If it is, it will keep the url as is. 
                     * If it isn't, it will prepend the begining of the url, and it will url encode the value
                     */
                    case 'create_url':
                        $regex_output = $this->spec->get_first_literal($resource, NS_CONV . 'url');
                        $regex_pattern = "/^(?!http.+)/";
                        if (preg_match($regex_pattern, $value)) {
                            $value = urlencode($value);
                            $value = str_replace("+", "%20", $value);
                            $value = preg_replace("${regex_pattern}", $regex_output, $value);
                        }
                        break;

                    case 'regex':
                        $regex_pattern = $this->spec->get_first_literal($resource, NS_CONV . 'regex_match');
                        foreach (array('%', '/', '@', '!', '^', ',', '.', '-') as $candidate_delimeter) {
                            if (strpos($candidate_delimeter, $regex_pattern) === false) {
                                $delimeter = $candidate_delimeter;
                                break;
                            }
                        }
                        //MVS: Added this as a correction, not sure what above foreach does but breaking the regex
                        $delimeter = "/";
                        $regex_output = $this->spec->get_first_literal($resource, NS_CONV . 'regex_output');
                        $value = preg_replace("${delimeter}${regex_pattern}${delimeter}", $regex_output, $value);
                        break;
//                    Now accesible under default
//                    case 'feet_to_metres':
//                        $value = Conversions::feet_to_metres($value);
//                        break;

                    case 'round':
                        $value = round($value);
                        break;

                    case 'substr':
                        $substr_start = $this->spec->get_first_literal($resource, NS_CONV . 'substr_start');
                        $substr_length = $this->spec->get_first_literal($resource, NS_CONV . 'substr_length');
                        $value = substr($value, $substr_start, $substr_length);
                        break;

                    default:
                        //When no built in function matches, a custom process function in called
                        //Made Conversion a little more flexible
                        if (method_exists("Conversions", $function))
                            $value = Conversions::$function($value);
                        else
                            throw new Exception("Unknown process requested: $function\n");
                }
            }
        }
        return $value;
    }

    public function lookup($source_column, $record, $lookup, $key) {
        if ($this->spec->get_subject_property_values($lookup, NS_CONV . 'lookup_entry')) {
            
            $this->lookup_config($lookup, $key);
            if ($this->lookups[$lookup][$key]) {
                if ( $this->lookups[$lookup][$key]['type'] == "lookup_column"){
                    $column_value['value'] = $this->get_record_value($record, $this->lookups[$lookup][$key]['value']);
                    return $column_value;
                }
                elseif($this->lookups[$lookup][$key]['type'] == "lookup_value"){
                    var_dump($this->lookups[$lookup][$key]['type']);
                    return $this->lookups[$lookup][$key]['value'];}
                }
        } else if ($this->spec->get_subject_property_values($lookup, NS_CONV . 'lookup_csv_file')) {
                return $this->lookup_csv_file($lookup, $key);
            }
    }

    function lookup_config($lookup, $key) {
        if (!isset($this->lookups[$lookup])) {
            $entries = $this->spec->get_subject_property_values($lookup, NS_CONV . 'lookup_entry');
            if (empty($entries)) {
                throw new Exception("Lookup ${lookup} had no lookup entries");
            }
            foreach ($entries as $entry) {
                //Accept lookups with several keys mapped to a single value
                $lookup_keys = $this->spec->get_subject_property_values($entry['value'], NS_CONV . 'lookup_key');
                $lookup_column = $this->spec->get_subject_property_values($entry['value'], NS_CONV . 'lookup_column');
                foreach ($lookup_keys as $lookup_key_array) {
                    $lookup_key = $lookup_key_array['value'];
                    if (isset($this->lookups[$lookup][$lookup_key])) {
                        throw new Exception("Lookup <${lookup}> contained a duplicate key");
                    }
                    $lookup_values = $this->spec->get_subject_property_values($entry['value'], NS_CONV . 'lookup_value');
                    if (count($lookup_values) > 1) {
                        throw new Exception("Lookup ${lookup} has an entry ${entry['value']} that does not have exactly one lookup value assigned.");
                    }
                    if ($lookup_column){
                            $this->lookups[$lookup][$lookup_key]['value'] = $lookup_column[0]['value'];
                            $this->lookups[$lookup][$lookup_key]['type'] = "lookup_column";
                    }
                    elseif ($lookup_values[0]){
                        $this->lookups[$lookup][$lookup_key]['value'] = $lookup_values[0];
                        $this->lookups[$lookup][$lookup_key]['type'] = "lookup_value";
                        }
                } 
            }
        }
        #return isset($this->lookups[$lookup][$key]) ? $this->lookups[$lookup][$key] : null;
    }
    
    function lookup_config_entries($lookup, $key) {
        if (!isset($this->lookups[$lookup])) {
            $entries = $this->spec->get_subject_property_values($lookup, NS_CONV . 'lookup_entry');
            if (empty($entries)) {
                throw new Exception("Lookup ${lookup} had no lookup entries");
            }
            foreach ($entries as $entry) {
                //Accept lookups with several keys mapped to a single value
                $lookup_keys = $this->spec->get_subject_property_values($entry['value'], NS_CONV . 'lookup_key');
                foreach ($lookup_keys as $lookup_key_array) {
                    $lookup_key = $lookup_key_array['value'];
                    if (isset($this->lookups[$lookup][$lookup_key])) {
                        throw new Exception("Lookup <${lookup}> contained a duplicate key");
                    }
                    $lookup_values = $this->spec->get_subject_property_values($entry['value'], NS_CONV . 'lookup_value');
                    if (count($lookup_values) != 1) {
                        throw new Exception("Lookup ${lookup} has an entry ${entry['value']} that does not have exactly one lookup value assigned.");
                    }
                    $this->lookups[$lookup][$lookup_key] = $lookup_values[0];
                }
            }
        }
        return isset($this->lookups[$lookup][$key]) ? $this->lookups[$lookup][$key] : null;
    }

    function lookup_csv_file($lookup, $key) {

        if (isset($this->lookups[$lookup]['keys']) AND isset($this->lookups[$lookup]['keys'][$key])) {
            return $this->lookups[$lookup]['keys'][$key];
        }

        $filename = $this->spec->get_first_literal($lookup, NS_CONV . 'lookup_csv_file');
        $key_column = $this->spec->get_first_literal($lookup, NS_CONV . 'lookup_key_column');
        $value_column = $this->spec->get_first_literal($lookup, NS_CONV . 'lookup_value_column');
        //retain file handle
        if (!isset($this->lookups[$lookup]['filehandle'])) {
            $this->lookups[$lookup]['filehandle'] = fopen($filename, 'r');
        }
        while ($row = fgetcsv($this->lookups[$lookup]['filehandle'])) {
            if ($row[$key_column] == $key) {
                $value = $row[$value_column];
                $this->lookups[$lookup]['keys'][$key] = $value;
                return $value;
            }
        }
        return false;
    }

}
