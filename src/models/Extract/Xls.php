<?php

namespace Extract;

/**
 * Xls model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Xls extends Type
{

    protected $table = 'input_xlsextract';

    protected $fillable = array('uri', 'sheet', 'has_header_row', 'start_row');

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

        $xls_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($xls_params);
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
            'has_header_row' => 'integer|min:0|max:1',
            'sheet' => 'required',
            'uri' => 'file|required',
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
                    'description' => 'The location of the CSV file, either a URL or a local file location.',
                    'type' => 'string',
                    'name' => 'URI',
                ),
                'has_header_row' => array(
                    'required' => false,
                    'description' => 'Boolean parameter defining if the separated value file contains a header row that contains the column names.',
                    'default_value' => 1,
                    'type' => 'boolean',
                    'name' => 'Header row',
                ),
                'sheet' => array(
                    'required' => true,
                    'description' => 'The name of the sheet to extract data from',
                    'default_value' => 'Sheet1',
                    'type' => 'string',
                    'name' => 'Sheet',
                ),
        );
    }
}
