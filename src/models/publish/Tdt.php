<?php

namespace publish;

use Eloquent;

/**
 * tdt publish model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Tdt extends Eloquent{

    protected $table = 'input_tdtpublish';

    protected $fillable = array('uri', 'user', 'password');

    /**
     * Relationship with Job
     */
    public function job(){
        return $this->morphOne('Job', 'publisher');
    }

    /**
     * Validate the input for this model and related models.
     */
    public static function validate($params){

        $tdt_params = array_only($params, array_keys(self::getCreateProperties()));
        return parent::validate($tdt_params);
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
            'uri' => 'required',
            //'user' => 'required',
            //'password' => 'required',
        );
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties(){

        return array(
                'uri' => array(
                    'required' => true,
                    'description' => 'The datatank uri to which the data will be published, consists of the datatank root uri, collection and resource name. (e.g. http://foo/definitions/trees/tree_resource)',
                ),
                'user' => array(
                    'required' => false,
                    'description' => 'The datatank user that has permission to add a datatank definition.',
                ),
                'password' => array(
                    'required' => false,
                    'description' => 'The datatank password of the user that has permission to add a datatank definition.',
                ),
        );
    }
}
