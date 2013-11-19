<?php

namespace extract;

use Eloquent;

/**
 * Ical model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Ical extends Eloquent{

    protected $table = 'input_icalextract';

    protected $fillable = array('uri');

    /**
     * Relationship with Job
     */
    public function job(){
        return $this->morphOne('Job', 'extractor');
    }

    /**
     * Validate the input for this model and related models.
     */
    public static function validate($params){

        $ical_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($ical_params);
    }

    /**
     * Retrieve the set of create parameters that make up a ICAL definition.
     * Include the parameters that make up relationships with this model.
     */
    public static function getAllProperties(){
        return self::getCreateProperties();
    }

    /**
     * Retrieve the set of validation rules for every create parameter.
     * If the parameters doesn't have any rules, it's not mentioned in the array.
     */
    public static function getCreateValidators(){
        return array(
            'uri' => 'file|required',
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties(){
        return array(
                'uri' => array(
                    'required' => true,
                    'description' => 'The location of the ICAL file, either a URL or a local file location.',
                ),
        );
    }
}
