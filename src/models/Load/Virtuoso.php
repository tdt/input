<?php

namespace Load;

/**
 * Virtuoso load model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Virtuoso extends Type
{
    protected $table = 'input_virtuosoload';

    protected $fillable = array('endpoint', 'username', 'password', 'port');

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
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties()
    {
        return array(
                'endpoint' => array(
                    'required' => true,
                    'description' => 'The endpoint of the virtuoso',
                    'type' => 'string',
                    'name' => 'Host',
                ),
                'port' => array(
                    'required' => true,
                    'description' => 'The port on which the virtuoso is listening.',
                    'type' => 'integer',
                    'name' => 'Port',
                    'default_value' => 8890,
                ),
                'username' => array(
                    'required' => false,
                    'description' => 'The username of the mongodb instance.',
                    'type' => 'string',
                    'name' => 'Username',
                ),
                'password' => array(
                    'required' => false,
                    'description' => 'The password of the user.',
                    'type' => 'string',
                    'name' => 'Password',
                ),
                'graph' => array(
                    'required' => false,
                    'description' => 'The name of the graph in which data must be stored, must be a URI!',
                    'type' => 'string',
                    'name' => 'Graph name',
                ),
        );
    }
}
