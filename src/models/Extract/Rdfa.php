<?php

namespace Extract;

/**
 * Rdfa model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Rdfa extends Type
{
    protected $table = 'input_rdfaextract';

    protected $fillable = array('uri');

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
                'description' => 'The location of the RDFa document, either a URL or a local file location.',
                'type' => 'string',
                'name' => 'URI',
            ),
            'base_uri' => array(
                'required' => true,
                'description' => 'The base URI of the document',
                'type' => 'string',
                'name' => 'Base URI',
            ),

        );
    }
}
