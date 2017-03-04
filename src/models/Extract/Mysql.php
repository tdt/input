<?php

namespace Extract;

/**
 * Mysql model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Mysql extends Type
{
    protected $table = 'input_mysqlextract';

    protected $fillable = [
        'collation',
        'database',
        'host',
        'password',
        'port',
        'query',
        'username',
    ];

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
        $mysql_params = array_only($params, array_keys(self::getCreateProperties()));

        return parent::validate($mysql_params);
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
            'database' => 'required',
            'host' => 'required',
            'port' => 'integer',
            'query' => 'required|mysqlquery',
            'username' => 'required',
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
                'name' => 'Host',
                'description' => 'The host of the MySQL database.',
                'type' => 'string',
            ),
            'port' => array(
                'required' => false,
                'name' => 'Port',
                'description' => 'The port of the MySQL database where a connection can be made to.',
                'type' => 'string',
                'default_value' => 3306
            ),
            'database' => array(
                'required' => true,
                'name' => 'Database',
                'description' => 'The name of the database where the datatable, that needs to be published, resides.',
                'type' => 'string',
            ),
            'username' => array(
                'required' => true,
                'name' => 'Username',
                'description' => 'A username that has read permissions on the provided datatable. Safety first, make sure the user only has read permissions.',
                'type' => 'string',
            ),
            'password' => array(
                'required' => false,
                'name' => 'Password',
                'description' => 'The password for the user that has read permissions.',
                'default_value' => '',
                'type' => 'string',
            ),
            'query' => array(
                'required' => true,
                'name' => 'Query',
                'description' => 'The query of which the results will be published as open data.',
                'type' => 'text'
            )
        );
    }
}
