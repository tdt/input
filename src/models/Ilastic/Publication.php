<?php namespace Ilastic;

use Jenssegers\Mongodb\Model as Eloquent;

class Publication extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'publications';

    protected $guarded = ['_id'];
}
