<?php

namespace Load;

/**
 * Elasticsearch load model
 *
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Elasticsearch extends Type
{
    protected $table = 'input_elasticsearchload';

    protected $fillable = array('host', 'username', 'password', 'port', 'es_index', 'es_type', 'schedule');

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
        $params = array_only($params, array_keys(self::getCreateProperties()));

        return parent::validate($params);

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
            'host' => 'required',
            'port' => 'required',
            'index' => 'required',
            'type' => 'required',
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties()
    {
        return array(
                'host' => array(
                    'required' => true,
                    'description' => "The host of the Elasticsearch, don't forget to include the http scheme.",
                    'type' => 'string',
                    'name' => 'Host',
                ),
                'port' => array(
                    'required' => true,
                    'description' => 'The port on which the Elasticsearch is listening, include the scheme (http, https) as well.',
                    'type' => 'integer',
                    'name' => 'Port',
                    'default_value' => 9200,
                ),
                'es_index' => array(
                    'required' => true,
                    'description' => 'The index to connect to.',
                    'type' => 'string',
                    'name' => 'Index',
                ),
                'es_type' => array(
                    'required' => false,
                    'description' => 'The type to insert the data into.',
                    'type' => 'string',
                    'name' => 'Type',
                ),
                'username' => array(
                    'required' => false,
                    'description' => 'The username of the Elasticsearch instance.',
                    'type' => 'string',
                    'name' => 'Username',
                ),
                'password' => array(
                    'required' => false,
                    'description' => 'The password of the user.',
                    'type' => 'string',
                    'name' => 'Password',
                )
        );
    }
}
