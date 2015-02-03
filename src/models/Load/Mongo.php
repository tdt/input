<?php

namespace Load;

use Eloquent;

/**
 * Mongo model
 *
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Mongo extends Eloquent
{

    protected $table = 'input_mongoload';

    protected $fillable = array('host', 'user', 'password', 'port', 'collection', 'database');

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

        $mongo_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($mongo_params);
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
            'collection' => 'required',
            'database' => 'required',
            'port' => 'required',
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
                    'description' => 'The host of the mongodb.',
                    'type' => 'string',
                    'name' => 'Host',
                ),
                'collection' => array(
                    'required' => true,
                    'description' => 'The collection in which the data needs to be stored.',
                    'type' => 'string',
                    'name' => 'Collection',
                ),
                'database' => array(
                    'required' => true,
                    'description' => 'The database name in which the collection resides to load the data in.',
                    'type' => 'string',
                    'name' => 'Database',
                ),
                'port' => array(
                    'required' => false,
                    'description' => 'The port of the mongodb to connect to.',
                    'default_value' => 27017,
                    'type' => 'integer',
                    'name' => 'Port',
                ),
                'user' => array(
                    'required' => false,
                    'description' => 'The username that has write permissions on the collection.',
                    'type' => 'string',
                    'name' => 'User',
                ),
                'password' => array(
                    'required' => false,
                    'description' => 'The password of the user.',
                    'type' => 'string',
                    'name' => 'Password',
                ),
        );
    }
}
