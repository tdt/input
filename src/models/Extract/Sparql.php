<?php

namespace Extract;

use Eloquent;

/**
 * Sparql model
 *
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Sparql extends Eloquent
{

    protected $table = 'input_sparqlextract';

    protected $fillable = array('query', 'endpoint', 'user', 'password');

    /**
     * Relationship with Job
     */
    public function job()
    {
        return $this->morphOne('Job', 'extractor');
    }

    /**
     * Validate the input for this model and related models.
     */
    public static function validate($params)
    {

        $sparql_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($sparql_params);
    }

    /**
     * Retrieve the set of create parameters that make up a SHP definition.
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
            'query' => 'required',
            'endpoint' => 'required',
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties()
    {
        return array(
                'query' => array(
                    'required' => true,
                    'description' => 'The query that fetches a set of data. This data will be used to put into the mongo, pagination will be done automatically.',
                    'type' => 'text',
                    'name' => 'Sparql query',
                ),
                'endpoint' => array(
                    'required' => true,
                    'description' => 'The endpoint to which the sparql query needs to be fired.',
                    'type' => 'text',
                    'name' => 'Sparql endpoint',
                ),
                'user' => array(
                    'required' => false,
                    'description' => 'The user that has read permissions on the given endpoint.',
                    'type' => 'text',
                    'name' => 'User',
                ),
                'password' => array(
                    'required' => false,
                    'description' => 'The password of the user.',
                    'type' => 'text',
                    'name' => 'Password',
                ),
        );
    }
}
