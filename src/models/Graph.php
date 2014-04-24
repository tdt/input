<?php


/**
 * Graph model
 * @copyright (C) 2011,2013 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
class Graph extends Eloquent
{

    protected $table = 'input_graph';

    protected $fillable = array('graph_id', 'graph_name');

}
