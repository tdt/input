<?php

namespace Extract;

/**
 * Geodcat model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Geodcat extends Type
{
    protected $table = 'input_geodcatextract';

    protected $fillable = ['uri', 'format'];

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
        $csv_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($csv_params);
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
            'uri' => 'required',
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties()
    {
        return array(
            'uri' => array(
                'required' => true,
                'description' => 'The URI of the GeoDCAT document.',
                'type' => 'string',
                'name' => 'URI',
            ),
            'format' => array(
                'required' => false,
                'description' => 'The format of the feed (ttl, rdfxml, ...)',
                'type' => 'string',
                'name' => 'Format',
                'default_value' => 'ttl'
            ),
        );
    }
}
