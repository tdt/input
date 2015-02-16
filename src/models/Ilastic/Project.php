<?php namespace Ilastic;

use Jenssegers\Mongodb\Model as Eloquent;

class Project extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'projects';

    protected $guarded = ['_id'];
}
