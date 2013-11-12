<?php

namespace map;

use Eloquent;

/**
 * Rdf mapper model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Rdf extends Eloquent{

    protected $table = 'rdfmap';

    protected $fillable = array('mapfile', 'base_uri');

    /**
     * Relationship with Job
     */
    public function job(){
        return $this->morphOne('Job', 'mapper');
    }

    /**
     * Validate the input for this model and related models.
     */
    public static function validate($params){

        $rdf_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($rdf_params);
    }

    /**
     * Retrieve the set of create parameters that make up a CSV definition.
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
            'mapfile' => 'file|required',
            'base_uri' => 'required',
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties(){
        return array(
                'mapfile' => array(
                    'required' => true,
                    'description' => 'The location of the CSV file, either a URL or a local file location.',
                ),
                'base_uri' => array(
                    'required' => true,
                    'description' => 'The base uri that will be used as a base for the subject of the triples.'
                ),
        );
    }
}
