<?php

namespace Load;

use Eloquent;

/**
 * Mongo load model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Mongo extends Eloquent
{
    protected $table = 'input_mongoload';

    protected $fillable = array('host', 'username', 'password', 'port', 'collection', 'database');

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
            'database' => 'required',
            'collection' => 'required',
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
                    'description' => 'The host of the mongodb',
                    'type' => 'string',
                    'name' => 'Host',
                ),
                'port' => array(
                    'required' => true,
                    'description' => 'The port on which the mongodb is listening.',
                    'type' => 'integer',
                    'name' => 'Endpoint',
                    'default_value' => 27017,
                ),
                'database' => array(
                    'required' => true,
                    'description' => 'The database to connect to.',
                    'type' => 'string',
                    'name' => 'Database',
                ),
                'collection' => array(
                    'required' => false,
                    'description' => 'The collection to insert the data into.',
                    'type' => 'string',
                    'name' => 'Collection',
                ),
        );
    }
}
