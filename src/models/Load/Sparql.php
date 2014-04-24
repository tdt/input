<?php

namespace Load;

use Eloquent;

/**
 * Sparql load model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Sparql extends Eloquent
{

    protected $table = 'input_sparqlload';

    protected $fillable = array('endpoint', 'user', 'password', 'buffer_size', 'graph_name');

    /**
     * Relationship with Job
     */
    public function job()
    {
        return $this->morphOne('Job', 'loader');
    }

    /**
     * Validate the input for this model and related models.
     */
    public static function validate($params)
    {

        $rdf_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($rdf_params);
    }

    /**
     * Retrieve the set of create parameters that make up a CSV definition.
     * Include the parameters that make up relationships with this model.
     */
    public static function getAllProperties()
    {
        return self::getCreateProperties();
    }

    /**
     * Retrieve the set of validation rules for every create parameter.
     * If the parameters doesn't have any rules, it's not mentioned in the array.
     */
    public static function getCreateValidators()
    {

        return array(
            'user' => 'required',
            'endpoint' => 'required',
            'password' => 'required',
            'buffer_size' => 'integer|min:1|max:124',
            'graph_name' => 'required',
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties()
    {

        return array(
                'user' => array(
                    'required' => true,
                    'description' => 'The username of the sparql endpoint.',
                ),
                'endpoint' => array(
                    'required' => true,
                    'description' => 'The endpoint that defines the sparql endpoint.',
                ),
                'password' => array(
                    'required' => true,
                    'description' => 'The password of the sparql endpoint, that provides together with the username credentials that have write permissions to the sparql endpoint.',
                ),
                'buffer_size' => array(
                    'required' => false,
                    'description' => 'The buffer size declares how many triples per insert query will be put.',
                    'default_value' => 4
                ),
                'graph_name' => array(
                    'required' => true,
                    'description' => 'The graph name that will serve as a basis to create the graph name to which triples will be inserted.',
                ),
        );
    }
}
