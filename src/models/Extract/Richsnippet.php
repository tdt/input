<?php

namespace Extract;

/**
 * Richsnippet model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Richsnippet extends Type
{
    protected $table = 'input_richsnippet_extract';

    protected $fillable = array('uri', 'follow_properties');

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
                'description' => 'The location of the HTML document enriched with semantic snippets (RDFa and/or JSON-LD)',
                'type' => 'string',
                'name' => 'URI',
            ),
            'base_uri' => array(
                'required' => true,
                'description' => 'The base URI of the document',
                'type' => 'string',
                'name' => 'Base URI',
            ),
            'follow_properties' => array(
                'required' => false,
                'description' => 'The properties that will be resolved if their objects are resources as well. These resources will be harvested as well. You can add multiple properties separated with a comma.',
                'type' => 'string',
                'name' => 'Resolve properties'
            )
        );
    }
}
