<?php


/**
 * Graph model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Graph extends Eloquent{

    protected $table = 'graphs';

    protected $fillable = array('graph_id', 'graph_name');

}
