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


        $properties = array();
        $source_definition = $this->source()->first();

        // Add all the properties that are mass assignable
        foreach($source_definition->getFillable() as $key){
            $properties[$key] = $source_definition->getAttributeValue($key);
        }

        // If the source type has a relationship with tabular columns, then attach those to the properties
        if(method_exists(get_class($source_definition), 'tabularColumns')){

            $columns = $source_definition->tabularColumns();
            $columns = $columns->getResults();

            $columns_props = array();
            foreach($columns as $column){
                $columns_props[$column->index] = array(
                    'column_name' => $column->column_name,
                    'is_pk' => $column->is_pk,
                    'column_name_alias' => $column->column_name_alias,
                );
            }

            $properties['columns'] = $columns_props;
        }

        return $properties;
    }

    /**
     * Delete the related source type
     */
    public function delete(){

        $source_type = $this->source()->first();
        $source_type->delete();

        parent::delete();
    }
}
