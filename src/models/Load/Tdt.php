<?php

namespace Load;

/**
 * Tdt load model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Tdt extends Type
{
    protected $table = 'input_tdtload';

    protected $fillable = ['type'];

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
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties()
    {
        return array(
            'definition_type' => array(
                'required' => true,
                'description' => 'The type of harvested resource (e.g. Inspire, Remote, ...).',
                'type' => 'string',
                'name' => 'Type',
                ),
            );
    }
}
