<?php

namespace Extract;

/**
 * Xml model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Xml extends Type
{

    protected $table = 'input_xmlextract';

    protected $fillable = array('uri', 'arraylevel', 'encoding');

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

        $xml_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($xml_params);
    }

    /**
     * Retrieve the set of create parameters that make up a xml definition.
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
            'uri' => 'file|required',
            'arraylevel' => 'required',
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
                    'description' => 'The location of the XML file, either a URL or a local file location.',
                    'type' => 'string',
                    'name' => 'URI',
                ),
                'arraylevel' => array(
                    'required' => true,
                    'description' => 'The level on which the objects that need to be mapped start. Example: <root><meta>...</meta><records><record>...</record></records>..., record starts at arraylevel 6 because textnodes also count as a level to be skipped.',
                    'type' => 'string',
                    'name' => 'Arraylevel',
                ),
                'encoding' => array(
                    'required' => false,
                    'description' => 'The type of encoding of the data. If no value is provided, the data encoding will default to UTF-8.',
                    'type' => 'list',
                    'list' => 'api/encodings',
                    'name' => 'Encoding'
                ),
        );
    }
}
