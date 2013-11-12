<?php



/**
 * Job model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Job extends Eloquent{

    protected $table = 'job';

    // These properties will be derived from the uri
    protected $fillable = array('name', 'collection_uri');

    /**
     * Relationship with an extractor
     */
    public function extractor(){
        return $this->morphTo();
    }

    /**
     * Relationship with a mapper
     */
    public function mapper(){
        return $this->morphTo();
    }

    /**
     * Relationship with a loader
     */
    public function loader(){
        return $this->morphTo();
    }

    /**
     * Relationship with a publisher
     */
    public function publisher(){
        return $this->morphTo();
    }

    /**
     * Return the properties ( = column fields ) for this model.
     */
    public static function getCreateProperties(){

        return array(

        );
    }

     /**
     * Retrieve the set of validation rules for every create parameter.
     * If the parameters doesn't have any rules, it's not mentioned in the array.
     */
    public static function getCreateValidators(){
        return array(
            'name' => 'unique:job|required',
        );
    }

    /**
     * Return all properties from a definition, including the properties of his relational objects
     */
    public function getAllProperties(){

        // Put all of the properties in an array
        $properties = array();

        // Get all of the properties of the job model
        foreach(self::getCreateProperties() as $property => $info){
            if(!empty($this->$property)){
                $properties[$property] = $this->$property;
            }
        }

        // Fill in the relationships in empl order
        $relations = array('extract' => $this->extractor()->first());

        // Don't fetch null relationships
        if(!empty($this->mapper_type)){
            $relations['map'] = $this->mapper()->first();
        }

        $relations['load'] = $this->loader()->first();

        // Don't fetch null relationships
        if(!empty($this->publisher_type)){
            $relations['publish'] = $this->publisher()->first();
        }

        // Add all the properties that are mass assignable
        foreach($relations as $key => $relation){

            // Get the type out of the classname
            $class_names = explode('\\', get_class($relation));
            $type = end($class_names);

            $type_properties = array('type' => $type);

            foreach($relation->getFillable() as $prop_key){
                $type_properties[$prop_key] = $relation->getAttributeValue($prop_key);
            }

            // Add the properties under the correct empl type
            $properties[$key] = $type_properties;
        }

        return $properties;
    }

    /**
     * Delete the related source type
     */
    public function delete(){

        // Fill in the relationships in empl order
        $relations = array('extract' => $this->extractor()->first());

        // Don't fetch null relationships
        if(!empty($this->mapper_id)){
            $relations['mapper'] = $this->mapper()->first();
        }

        $relations['loader'] = $this->loader()->first();

        // Don't fetch null relationships
        if(!empty($this->publisher_id)){
            $relations['publisher'] = $this->publisher()->first();
        }

        // Delete the job's relationships
        foreach($relations as $key => $relation){
            $relation->delete();
        }

        parent::delete();
    }
}
