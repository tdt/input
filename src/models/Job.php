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
        return $this->morphTo()
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
}
