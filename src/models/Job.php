<?php



/**
 * Job model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Job extends Eloquent{

    protected $table = 'job';

    protected $fillable = array("name");

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
     * Return all properties from a definition, including the properties of his relational objects
     */
    public function getAllProperties(){

        // Put all of the properties in an array
        $properties = array();

        // Fill in the relationships in empl order
        $relations = array('extract' => $this->extractor()->first());

        // Don't fetch null relationships
        if(!empty($this->mapper_type)){
            $relations['mapper'] = $this->mapper()->first();
        }

        $relations['loader'] = $this->loader()->first();

        // Don't fetch null relationships
        if(!empty($this->publisher_type)){
            $relations['publisher'] = $this->publisher()->first();
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
